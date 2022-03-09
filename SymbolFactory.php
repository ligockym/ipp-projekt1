<?php

enum SYMBOL_TYPE
{
    case VARIABLE;
    case CONSTANT;
    case LABEL;
    case TYPE;
}

class SymbolFactory
{
    /**
     * Create symbol from "LF@var" to new Variable, or from "string@hello" to new Constant
     * @param string $expr
     * @param bool $is_it_label determines whether instruction accepts label as argument
     * @return Symbol
     * @throws Exception
     */
    static function parse_and_create(string $expr, bool $is_it_label): Symbol
    {
        $symbol_type = DATA_TYPE::VAR;
        $symbol_frame = ''; // in case of LF / TF / GF
        $symbol_value = ''; // name of variable / literal / number

        if ($is_it_label && preg_match('/^([_\-$&%*!?a-zA-Z][_\-$&%*!?\w]*)$/', $expr)) {
            $symbol_type = DATA_TYPE::LABEL;
            $symbol_value = $expr; // for label use whole
        } else if (preg_match('/^string@(.*)$/', $expr, $matches)) {
            // is string -> check if it does not contain any of non-allowed constructions
            $value = $matches[1];

            // CHAR => 1
            // \ => 2
            $state = 1;
            $char_i = 0;
            $str_len = mb_strlen($value);

            $throw_is_not_allowed = function ($char) {
                if (mb_ord($char) <= 32 || mb_ord($char) == 35) { // or #
                    // character is in [000-032, 035] -> error
                    throw new Exception("Character in 000-032, 035 asci value was not escaped.");
                }
            };

            while ($str_len > ($char_i)) { // for each char
                $char = mb_substr($value, $char_i, 1);
                switch ($state) {
                    case 1: // CHAR
                        $throw_is_not_allowed($char);
                        if ($char == '\\') $state = 2; // to \
                        else $state = 1; // to CHAR
                        break;
                    case 2: // \
                        // 4 characters excluding \, so check if three digits followed by non digit are present
                        $substr = mb_substr($value, $char_i, 3);
                        if (preg_match('/^\d{3}$/', $substr)) {
                            // found -> it is valid \xxx
                            $state = 1;
                        } else {
                            // did not find -> error, not valid \xxx
                            throw new Exception("Wrongly escaped sequence: '$substr'");
                        }
                        break;
                }
                $char_i++;
            }
            if ($state == 2) {
                // we ended in slash state -> error
                throw new Exception("Wrongly escaped sequence\n");
            }

            $symbol_value = $value;
            $symbol_type = DATA_TYPE::STRING;
        } else if (preg_match('/^int@(.+)$/', $expr, $matches)) {
            $value = $matches[1];
            if (!preg_match('/^[-+]?\d*$/', $value)) {
                throw new Exception("Wrong integer format.");
            }
            $symbol_value = $value;
            $symbol_type = DATA_TYPE::INT;
        } else if (preg_match('/^bool@(false|true)$/', $expr, $matches)) {
            $symbol_value = $matches[1];
            $symbol_type = DATA_TYPE::BOOL;
        } else if (preg_match('/^nil@nil$/', $expr)) {
            $symbol_value = 'nil';
            $symbol_type = DATA_TYPE::NIL;
        } else if (preg_match('/^(LF|GF|TF)@([_\-$&%*!?a-zA-Z][_\-$&%*!?\w]*)$/', $expr, $matches)) {
            $symbol_type = DATA_TYPE::VAR;
            $symbol_frame = $matches[1];
            $symbol_value = $matches[2];
        } else if (preg_match('/^(int|string|bool)$/', $expr, $matches)) {
            $symbol_type = DATA_TYPE::TYPE;
            $symbol_value = DATA_TYPE::tryFrom($matches[1]);
            if (!$symbol_value) {
                throw new Exception("Wrong type");
            }
        } else {
            throw new Exception("Wrong argument type 2: '$expr'");
        }

        // Returns correct symbol
        if ($symbol_type == DATA_TYPE::INT ||
            $symbol_type == DATA_TYPE::BOOL ||
            $symbol_type == DATA_TYPE::NIL ||
            $symbol_type == DATA_TYPE::STRING) {
            return new Constant($symbol_type, $symbol_value);
        } else if ($symbol_type == DATA_TYPE::TYPE) {
            return new Type($symbol_value);
        } else if ($symbol_type == DATA_TYPE::VAR) {
            return new Variable($symbol_value, $symbol_frame);
        } else if ($symbol_type == DATA_TYPE::LABEL) {
            return new Label($symbol_value);
        } else {
            throw new Exception("Wrong argument format");
        }
    }
}