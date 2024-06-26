<?php

use Google\Protobuf\Internal\Message;
use Google\Protobuf\Struct;
use function Swoole\Coroutine\map;

if (!function_exists('toArray')) {
    /**
     * @param string|array $value
     * @return array
     */
    function toArray(string|array $value): array
    {
        return array_filter(is_array($value) ? $value : (is_string($value) ? explode(",", $value) : []));
    }
}

if (!function_exists('di')) {
    function di(string $id)
    {
        return \Hyperf\Context\ApplicationContext::getContainer()->get($id);
    }
}

if (!function_exists('struct_to_array')) {
    function struct_to_array(Struct|Message|null $struct): array
    {
        if (!$struct) {
            return [];
        }
        return $struct instanceof Struct ? json_decode($struct->serializeToJsonString(), true) : message_to_array($struct);
    }
}

if (!function_exists('message_to_array')) {
    /**
     * proto message 对象转数组,需要消耗性能的
     */
    function message_to_array(Message $object): array
    {
        $reflectionClass = new ReflectionClass(get_class($object));
        $array = array();
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            if ($value instanceof \Google\Protobuf\Internal\RepeatedField) {
                // repeated
                $array[$property->getName()] = repeated_field_to_array($value);
            } elseif ($value instanceof Struct) {
                // struct
                $array[$property->getName()] = struct_to_array($value);
            } elseif ($value instanceof Message) {
                // message
                $array[$property->getName()] = message_to_array($value);
            } else {
                $array[$property->getName()] = $value;
            }
            $property->setAccessible(false);
        }
        return $array;
    }
}

if (!function_exists('array_to_struct')) {
    function array_to_struct(array $array): Struct
    {
        $struct = new Struct();
        try {
            $struct->mergeFromJsonString(json_encode($array));
        } catch (Exception $e) {
        }
        return $struct;
    }
}

if (!function_exists("repeated_field_to_array")) {
    function repeated_field_to_array(\Google\Protobuf\Internal\RepeatedField $repeatedField, array|string|null $fields = null): array
    {
        if ($repeatedField->getType() !== \Google\Protobuf\Internal\GPBType::MESSAGE) {
            return iterator_to_array($repeatedField);
        }

        $fields && !is_array($fields) && $fields = explode(',', $fields);
        return map(iterator_to_array($repeatedField), function (Message|Struct $item) use ($fields) {
            return !$fields ? struct_to_array($item) : array_intersect_key(struct_to_array($item), array_flip($fields));
        });
    }
}
if (!function_exists("array_merge_by_key")) {
    function array_merge_by_key(array $array, string|int $key, $column = ""): array
    {
        $new = [];
        foreach ($array as $item) {
            if (isset($item[$key])) $new[$item[$key]][] = $column != "" ? array_column($item, $column) : $item;
        }
        return $new;
    }
}

if (!function_exists("check_param_and_call")) {
    function check_param_and_call(array $param, string|array $fields, callable $fun): void
    {
        $fields = is_array($fields) ? $fields : explode(",", $fields);
        foreach ($fields as $field) {
            if (isset($param[$field]) && $param[$field] != "") {
                $fun($field, $param[$field]);
            }
        }
    }
}


if (!function_exists("check_db_connect")) {
    function check_db_connect($max = 5, $pool = "default"): void
    {
        // 非启动程序 就不检查连接
        if ((new \Symfony\Component\Console\Input\ArgvInput())->getFirstArgument() != 'start') {
            return;
        }
        $tryTimes = 0;
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        while ($tryTimes < $max) {
            $tryTimes++;
            $output->writeln("$tryTimes 次尝试连接");
            try {
                \Hyperf\DbConnection\Db::connection($pool)->selectOne("select 1");
            } catch (\Throwable $throwable) {
                $output->writeln("error: " . $throwable->getMessage());
                if ($tryTimes == $max) throw $throwable;
                sleep($tryTimes);
                continue;
            }
            break;
        }
    }
}