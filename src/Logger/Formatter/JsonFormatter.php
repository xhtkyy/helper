<?php
declare(strict_types=1);
/**
 * @author   crayxn <https://github.com/crayxn>
 * @contact  crayxn@qq.com
 */

namespace Xhtkyy\Helper\Logger\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Stringable;
use Throwable;
use Monolog\LogRecord;

/**
 * Encodes whatever record data is passed to it as json
 *
 * This can be useful to log to databases or remote APIs
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class JsonFormatter extends NormalizerFormatter
{
    public const BATCH_MODE_JSON = 1;
    public const BATCH_MODE_NEWLINES = 2;

    /** @var self::BATCH_MODE_* */
    protected int $batchMode;

    protected bool $appendNewline;

    protected bool $ignoreEmptyContextAndExtra;

    protected bool $includeStacktraces = false;

    private array $replaceKeys = [
        'datetime' => 'time',
        'message' => 'msg'
    ];

    private array $withOtherKeys = [];

    /**
     * @param array|null $withOtherKeys
     * @param array|null $replaceKeys
     * @param string|null $dateFormat
     * @param bool $includeStacktraces
     * @throws \RuntimeException If the function json_encode does not exist
     */
    public function __construct(?array $withOtherKeys = null, ?array $replaceKeys = null, ?string $dateFormat = null, bool $includeStacktraces = false)
    {
        $this->batchMode = self::BATCH_MODE_JSON;
        $this->appendNewline = true;
        $this->ignoreEmptyContextAndExtra = true;
        $this->includeStacktraces = $includeStacktraces;

        $withOtherKeys && $this->withOtherKeys = $withOtherKeys;
        $replaceKeys && $this->replaceKeys = $replaceKeys;

        parent::__construct($dateFormat);
    }

    /**
     * The batch mode option configures the formatting style for
     * multiple records. By default, multiple records will be
     * formatted as a JSON-encoded array. However, for
     * compatibility with some API endpoints, alternative styles
     * are available.
     */
    public function getBatchMode(): int
    {
        return $this->batchMode;
    }

    /**
     * True if newlines are appended to every formatted record
     */
    public function isAppendingNewlines(): bool
    {
        return $this->appendNewline;
    }

    /**
     * @inheritDoc
     */
    public function format(LogRecord $record): string
    {
        $normalized = parent::format($record);


        //format level name
        if (isset($normalized['level']) && isset($normalized['level_name'])) {
            $normalized['level'] = strtolower($normalized['level_name']);
            unset($normalized['level_name']);
        }

        if (isset($normalized['context'])) {
            $normalized = array_merge($normalized, $normalized['context'] ?? []);
            unset($normalized['context']);
        }

        if (isset($normalized['extra']) && $normalized['extra'] === []) {
            if ($this->ignoreEmptyContextAndExtra) {
                unset($normalized['extra']);
            } else {
                $normalized['extra'] = new \stdClass;
            }
        }

        if (isset($normalized['trace']) && !$this->includeStacktraces) {
            unset($normalized['trace']);
        }

        //with other keys
        $normalized = array_merge($normalized, $this->withOtherKeys);

        //replace keys
        foreach ($this->replaceKeys as $key => $newKey) {
            if (isset($normalized[$key])) {
                $normalized[$newKey] = $normalized[$key];
                unset($normalized[$key]);
            }
        }

        return $this->toJson($normalized, true) . ($this->appendNewline ? "\n" : '');
    }

    /**
     * @inheritDoc
     */
    public function formatBatch(array $records): string
    {
        return match ($this->batchMode) {
            static::BATCH_MODE_NEWLINES => $this->formatBatchNewlines($records),
            default => $this->formatBatchJson($records),
        };
    }

    /**
     * @return $this
     */
    public function includeStacktraces(bool $include = true): self
    {
        $this->includeStacktraces = $include;

        return $this;
    }

    /**
     * Return a JSON-encoded array of records.
     *
     * @phpstan-param LogRecord[] $records
     */
    protected function formatBatchJson(array $records): string
    {
        return $this->toJson($this->normalize($records), true);
    }

    /**
     * Use new lines to separate records instead of a
     * JSON-encoded array.
     *
     * @phpstan-param LogRecord[] $records
     */
    protected function formatBatchNewlines(array $records): string
    {
        $oldNewline = $this->appendNewline;
        $this->appendNewline = false;
        $formatted = array_map(fn(LogRecord $record) => $this->format($record), $records);
        $this->appendNewline = $oldNewline;

        return implode("\n", $formatted);
    }

    /**
     * Normalizes given $data.
     *
     * @return null|scalar|array<mixed[]|scalar|null|object>|object
     */
    protected function normalize(mixed $data, int $depth = 0): mixed
    {
        if ($depth > $this->maxNormalizeDepth) {
            return 'Over ' . $this->maxNormalizeDepth . ' levels deep, aborting normalization';
        }

        if (is_array($data)) {
            $normalized = [];

            $count = 1;
            foreach ($data as $key => $value) {
                if ($count++ > $this->maxNormalizeItemCount) {
                    $normalized['...'] = 'Over ' . $this->maxNormalizeItemCount . ' items (' . count($data) . ' total), aborting normalization';
                    break;
                }

                $normalized[$key] = $this->normalize($value, $depth + 1);
            }

            return $normalized;
        }

        if (is_object($data)) {
            if ($data instanceof \DateTimeInterface) {
                return $this->formatDate($data);
            }

            if ($data instanceof Throwable) {
                return $this->normalizeException($data, $depth);
            }

            // if the object has specific json serializability we want to make sure we skip the __toString treatment below
            if ($data instanceof \JsonSerializable) {
                return $data;
            }

            if ($data instanceof Stringable) {
                return $data->__toString();
            }

            return $data;
        }

        if (is_resource($data)) {
            return parent::normalize($data);
        }

        return $data;
    }

    /**
     * Normalizes given exception with or without its own stack trace based on
     * `includeStacktraces` property.
     *
     * @inheritDoc
     */
    protected function normalizeException(Throwable $e, int $depth = 0): array
    {
        $data = parent::normalizeException($e, $depth);
        if (!$this->includeStacktraces) {
            unset($data['trace']);
        }

        return $data;
    }
}