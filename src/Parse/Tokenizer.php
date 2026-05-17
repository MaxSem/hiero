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

        $splitted = preg_split('/(\s*[*:!.]+\s*|[-\s]+)/', $input, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($splitted)) {
            throw new HieroException('Regexp error');
        }

        $result = [];
        foreach ($splitted as $token) {
            if ($token === '') {
                continue;
            }

            $token = preg_replace(self::DELIMITER_REGEX, '', $token)
                ?? throw new HieroException('Regex error');

            if ($token === '') {
                $token = Token::SEPARATOR;
            }

            if (Token::isVoid($token)) {
                if (array_last($result) !== Token::SEPARATOR) {
                    $result[] = Token::SEPARATOR;
                }
            }

            if ($token !== Token::SEPARATOR || array_last($result) !== Token::SEPARATOR) {
                $result[] = $token;
            }

            if (Token::isVoid($token)) {
                $result[] = Token::SEPARATOR;
            }
        }

        return $result;
    }
}
