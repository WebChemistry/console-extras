<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Bridge\Sentry;

use LogicException;
use Sentry\CheckInStatus;
use Sentry\MonitorConfig;
use Sentry\MonitorSchedule;
use WebChemistry\ConsoleExtras\ExtraCommand;
use WebChemistry\ConsoleExtras\Extractor\CommandJobExtractor;
use function Sentry\captureCheckIn;

final class SentryIntegration
{

	public function integrate(ExtraCommand $command): void
	{
		$job = (new CommandJobExtractor())->extract($command);

		if (!$job) {
			throw new LogicException(sprintf('Command %s has no job.', $command::class));
		}

		$monitorConfig = new MonitorConfig(MonitorSchedule::crontab($job->schedule), 5, 10);

		$start = null;

		$command->onStart[] = function () use ($job, $monitorConfig, &$start): void {
			captureCheckIn($job->slug, CheckInStatus::inProgress(), monitorConfig: $monitorConfig);

			$start = microtime(true);
		};

		$command->onSuccess[] = function () use ($job, $monitorConfig, &$start): void {
			$duration = null;

			if ($start !== null) {
				$duration = microtime(true) - $start;
			}

			captureCheckIn($job->slug, CheckInStatus::ok(), $duration, $monitorConfig);
		};

		$command->onError[] = function () use ($job, $monitorConfig, &$start): void {
			$duration = null;

			if ($start !== null) {
				$duration = microtime(true) - $start;
			}

			captureCheckIn($job->slug, CheckInStatus::error(), $duration, $monitorConfig);
		};
	}

}
