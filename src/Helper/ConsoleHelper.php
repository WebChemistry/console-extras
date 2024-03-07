<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Helper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use WebChemistry\ConsoleExtras\Exception\ErroneouslyTerminateCommand;
use WebChemistry\ConsoleExtras\Exception\SuccessfullyTerminateCommand;

final class ConsoleHelper
{

	private bool $errors = false;

	public function __construct(
		private Command $command,
		private InputInterface $input,
		private OutputInterface $output,
	)
	{
	}

	public function configureStyles(): void
	{
		$formatter = $this->output->getFormatter();

		$formatter->setStyle('strong', new OutputFormatterStyle(options: [
			'bold',
		]));
		$formatter->setStyle('error', new OutputFormatterStyle('red'));
		$formatter->setStyle('success', new OutputFormatterStyle('green'));
		$formatter->setStyle('green', new OutputFormatterStyle('green'));
		$formatter->setStyle('warning', new OutputFormatterStyle('bright-yellow'));
	}

	public function question(string $question, bool $default = false): bool
	{
		$helper = $this->command->getHelper('question');

		assert($helper instanceof QuestionHelper);

		$defaultTxt = $this->output->getFormatter()->format(
			sprintf('<comment>[%s]</comment>', $default ? 'yes' : 'no')
		);

		/** @var bool */
		return $helper->ask(
			$this->input,
			$this->output,
			new ConfirmationQuestion(sprintf('%s %s ', $question, $defaultTxt), $default),
		);
	}

	public function fatalError(string $error): never
	{
		$this->error($error);

		$this->terminate();
	}

	public function error(string $error, bool $prependErrorText = true): void
	{
		$this->errors = true;

		if (!$this->output->getFormatter()->hasStyle('error')) {
			$this->output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));
		}

		if ($prependErrorText) {
			$error = sprintf('Error: %s', $error);
		}

		$this->output->writeln(sprintf('<error>%s</error>', $error));
	}

	public function success(string $message): void
	{
		if (!$this->output->getFormatter()->hasStyle('success')) {
			$this->output->getFormatter()->setStyle('success', new OutputFormatterStyle('green'));
		}

		$this->output->writeln(sprintf('<success>%s</success>', $message));
	}

	public function comment(string $message): void
	{
		$this->output->writeln(sprintf('<comment>%s</comment>', $message));
	}

	public function warning(string $message, bool $prependWarningText = true): void
	{
		if (!$this->output->getFormatter()->hasStyle('warning')) {
			$this->output->getFormatter()->setStyle('warning', new OutputFormatterStyle('bright-yellow'));
		}

		if ($prependWarningText) {
			$message = sprintf('Warning: %s', $message);
		}

		$this->output->writeln(sprintf('<warning>%s</warning>', $message));
	}

	public function confirmToContinue(bool $default = false, string $message = 'Continue?'): void
	{
		$result = $this->question($message, $default);

		if (!$result) {
			throw new SuccessfullyTerminateCommand();
		}
	}

	public function terminate(?bool $success = null): never
	{
		if ($success === false || ($success === null && $this->errors)) {
			throw new ErroneouslyTerminateCommand();
		}

		throw new SuccessfullyTerminateCommand();
	}

	public function terminateIfErrors(): void
	{
		if ($this->errors) {
			$this->terminate(false);
		}
	}

}
