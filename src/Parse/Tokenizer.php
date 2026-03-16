<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\HieroException;

final readonly class Tokenizer
{
    private const DELIMITER_REGEX = '/([-\s]+)/';

    /**
     * @return string[]
     */
    public function tokenize(string $input): array
    {
        $input = trim($input);

        if ($input === '') {
            return [];
        }

        $splitted = preg_split('/(\s*[*:!]\s*|[-\s]+)/', $input, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($splitted)) {
            throw new HieroException('Regexp error');
        }

        $result = [];
        foreach ($splitted as $token) {
            if ($token === '') {
                continue;
            }
            if (preg_replace(self::DELIMITER_REGEX, '', $token) === '') {
                $token = Token::SEPARATOR;
            }
            $result[] = trim($token);
        }

        return $result;
    }
}
