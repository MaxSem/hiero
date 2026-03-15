<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\HieroException;

final readonly class Tokenizer
{
    /**
     * @return string[]
     */
    public function tokenize(string $input): array
    {
        $input = trim($input);

        if ($input === '') {
            return [];
        }

        $splitted = preg_split('/(\s+|[-:*!])/', $input, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($splitted)) {
            throw new HieroException('Regexp error');
        }

        $result = [];
        foreach ($splitted as $token) {
            $token = trim($token);
            if ($token !== '' && $token !== '-') {
                $result[] = $token;
            }
        }

        return $result;
    }
}
