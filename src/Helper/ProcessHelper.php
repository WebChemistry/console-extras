<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Helper;

use RuntimeException;
use Symfony\Component\Process\Process;

final class ProcessHelper
{

	/** @var resource|null */
	private static $stdout;
	/** @var resource|null */
	private static $stderr;

	private ?string $currentDirectory = null;

	public function __construct(
		private bool $tty = true,
	)
	{
	}

	public function withCurrentDirectory(string $directory): self
	{
		$clone = clone $this;
		$clone->currentDirectory = $directory;

		return $this;
	}

	/**
	 * @param array<string|int>|Process $command
	 */
	public function stream(array|Process $command): Process
	{
		if (!$command instanceof Process) {
			$process = new Process($command, $this->currentDirectory);
		} else {
			$process = $command;
		}

		if ($this->tty && $process::isTtySupported()) {
			$process->setTty(true);
			$process->run();
		} else {
			$process->run(function (string $type, $buffer): void {
				if (Process::ERR === $type) {
					fwrite(self::getStderr(), $buffer);
				} else {
					fwrite(self::getStdout(), $buffer);
				}
			});
		}

		return $process;
	}

	/**
	 * @return resource
	 */
	private static function getStdout()
	{
		if (!isset(self::$stdout)) {
			$stdout = fopen('php://stdout', 'w');

			if (!$stdout) {
				throw new RuntimeException('Unable to open stdout.');
			}

			self::$stdout = $stdout;
		}

		return self::$stdout;
	}

	/**
	 * @return resource
	 */
	private static function getStderr()
	{
		if (!isset(self::$stderr)) {
			$stderr = fopen('php://stderr', 'w');

			if (!$stderr) {
				throw new RuntimeException('Unable to open stderr.');
			}

			self::$stderr = $stderr;
		}

		return self::$stderr;
	}

	public function __destruct()
	{
		if (isset(self::$stderr)) {
			fclose(self::$stderr);
		}

		if (isset(self::$stdout)) {
			fclose(self::$stdout);
		}
	}

}
