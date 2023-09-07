<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras;

use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use WebChemistry\ConsoleExtras\Attribute\Description;
use WebChemistry\ConsoleExtras\Exception\InvalidCommandValueException;
use WebChemistry\ConsoleExtras\Extractor\CommandArgument;
use WebChemistry\ConsoleExtras\Extractor\CommandOption;
use WebChemistry\ConsoleExtras\Extractor\CommandPropertyExtractor;
use WebChemistry\ConsoleExtras\Builder\CommandDefinitionPropertyBuilder;
use WebChemistry\ConsoleExtras\Setup\CommandPropertySetup;

abstract class ExtraCommand extends Command
{

	/** @var CommandArgument[] */
	private array $arguments;

	/** @var CommandOption[] */
	private array $options;

	private ValidatorInterface $validator;

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

	final protected function execute(InputInterface $input, OutputInterface $output): int
	{
		try {
			$values = (new CommandPropertySetup())->setup($this->arguments, $this->options, $input);
		} catch (InvalidCommandValueException $e) {
			$output->writeln($e->getMessage());

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

				return self::FAILURE;
			}
		}

		$this->exec($input, $output);

		return self::SUCCESS;
	}

	abstract protected function exec(InputInterface $input, OutputInterface $output): bool;

}
