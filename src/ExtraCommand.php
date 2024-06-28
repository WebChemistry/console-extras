<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras;

use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use WebChemistry\ConsoleExtras\Attribute\Description;
use WebChemistry\ConsoleExtras\Builder\CommandDefinitionPropertyBuilder;
use WebChemistry\ConsoleExtras\Exception\InvalidCommandValueException;
use WebChemistry\ConsoleExtras\Exception\TerminateCommand;
use WebChemistry\ConsoleExtras\Extractor\CommandArgument;
use WebChemistry\ConsoleExtras\Extractor\CommandOption;
use WebChemistry\ConsoleExtras\Extractor\CommandPropertyExtractor;
use WebChemistry\ConsoleExtras\Helper\ConsoleHelper;
use WebChemistry\ConsoleExtras\Setup\CommandPropertySetup;

abstract class ExtraCommand extends Command
{

	/** @var CommandArgument[] */
	private array $arguments;

	/** @var CommandOption[] */
	private array $options;

	private ValidatorInterface $validator;

	protected ConsoleHelper $helper;

	/** @var array<callable(static $command, InputInterface $input, OutputInterface $output): void> */
	public array $onStart = [];

	/** @var array<callable(static $command, InputInterface $input, OutputInterface $output): void> */
	public array $onSuccess = [];

	/** @var array<callable(static $command, InputInterface $input, OutputInterface $output): void> */
	public array $onError = [];

	private float $startTime;

	public function __construct(?string $name = null)
	{
		parent::__construct($name);

		$this->_configure();
	}

	public function setValidator(ValidatorInterface $validator): static
	{
		$this->validator = $validator;

		return $this;
	}

	private function _configure(): void
	{
		$extractor = new CommandPropertyExtractor();
		[$this->arguments, $this->options] = $extractor->extract($this);

		$parser = new CommandDefinitionPropertyBuilder();
		$parser->build($this->arguments, $this->options, $this->getDefinition());

		$reflection = new ReflectionClass($this);
		$attrs = $reflection->getAttributes(Description::class);

		if ($attrs) {
			/** @var Description $attr */
			$attr = $attrs[0]->newInstance();

			$this->setDescription($attr->description);
		}
	}

	protected function initialize(InputInterface $input, OutputInterface $output): void
	{
		$this->helper = new ConsoleHelper($this, $input, $output);
	}

	/**
	 * @throws Throwable
	 */
	public function run(InputInterface $input, OutputInterface $output): int
	{
		try {
			return parent::run($input, $output);
		} catch (Throwable $exception) {
			foreach ($this->onError as $callback) {
				$callback($this, $input, $output);
			}

			throw $exception;
		}
	}

	final protected function execute(InputInterface $input, OutputInterface $output): int
	{
		foreach ($this->onStart as $callback) {
			$callback($this, $input, $output);
		}

		try {
			$values = (new CommandPropertySetup())->setup($this->arguments, $this->options, $input);
		} catch (InvalidCommandValueException $e) {
			$output->writeln($e->getMessage());

			foreach ($this->onError as $callback) {
				$callback($this, $input, $output);
			}

			return self::FAILURE;
		}

		foreach ($values as $name => $value) {
			$this->{$name} = $value;
		}

		if (isset($this->validator)) {
			$errors = $this->validator->validate($this);

			if ($errors->count() > 0) {
				foreach ($errors as $error) {
					$output->writeln(sprintf('%s: %s', $error->getPropertyPath(), $error->getMessage()));
				}

				foreach ($this->onError as $callback) {
					$callback($this, $input, $output);
				}

				return self::FAILURE;
			}
		}

		$this->startTime = microtime(true);

		try {
			$this->exec($input, $output);
		} catch (Throwable $exception) {
			if ($exception instanceof TerminateCommand) {
				if ($exception->getTerminateCode() === self::SUCCESS) {
					foreach ($this->onSuccess as $callback) {
						$callback($this, $input, $output);
					}
				} else {
					foreach ($this->onError as $callback) {
						$callback($this, $input, $output);
					}
				}

				return $exception->getTerminateCode();
			}

			foreach ($this->onError as $callback) {
				$callback($this, $input, $output);
			}

			throw $exception;
		}

		foreach ($this->onSuccess as $callback) {
			$callback($this, $input, $output);
		}

		return self::SUCCESS;
	}

	abstract protected function exec(InputInterface $input, OutputInterface $output): void;

	protected function printDevelopmentInfo(OutputInterface $output): void
	{
		$output->writeln('');
		$output->writeln(sprintf(
			'Execution time: %sms, Memory Peak: %sMB',
			$this->formatNumber((microtime(true) - $this->startTime) * 1000),
			$this->formatNumber(memory_get_peak_usage(true) / 1024 / 1024),
		));
	}

	private function formatNumber(float|int $num): string
	{
		return rtrim(rtrim(number_format($num, 2), '0'), '.');
	}

}
