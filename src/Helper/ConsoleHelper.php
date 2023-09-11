<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Helper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use WebChemistry\ConsoleExtras\Exception\TerminateCommandException;

final class ConsoleHelper
{

	public function __construct(
		private Command $command,
		private InputInterface $input,
		private OutputInterface $output,
	)
	{
	}

	public function question(string $question, bool $default = false): bool
	{
		$helper = $this->command->getHelper('question');

		assert($helper instanceof QuestionHelper);

		$defaultTxt = $this->output->getFormatter()->format(
			sprintf('<comment>[%s]</comment>', $default ? 'yes' : 'no')
		);

		return $helper->ask(
			$this->input,
			$this->output,
			new ConfirmationQuestion(sprintf('%s %s ', $question, $defaultTxt), $default),
		);
	}

	public function warning(string $message): void
	{
		if (!$this->output->getFormatter()->hasStyle('warning')) {
			$this->output->getFormatter()->setStyle('warning', new OutputFormatterStyle('bright-yellow'));
		}

		$this->output->writeln(sprintf('<warning>Warning: %s</warning>', $message));
	}

	public function confirmToContinue(bool $default = false, string $message = 'Continue?'): void
	{
		$result = $this->question($message, $default);

		if (!$result) {
			throw new TerminateCommandException();
		}
	}

}
