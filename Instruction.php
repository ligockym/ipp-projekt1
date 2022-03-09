<?php

class Instruction
{
    private int $order;
    private INSTR_TYPE $type;

    /**
     * @var Symbol[]
     */
    private array $args;


    /**
     * Tries to create an instruction from the whole line
     * @param string $line has to contain at least one non-white character (not space, nor tabulator)
     * @param INSTR_TYPE[] $allowed_label all instructions which can be labels
     */
    public function __construct(string $line, array $allowed_label, int $order)
    {
        $this->order = $order;

        $this->args = [];
        $words = $this->break_line_to_words($line);

        if (count($words) == 0) {
            // Error, empty instruction, skip, should never happen, because trimming is implemented
            exit("Instruction error: Line is empty");
        }
        $instruction_type = $this->what_instruction_type($words[0]);

        if ($instruction_type == null) {
            // did not find instruction, not a valid one
            echo "Wrong op code for '$line'";
            exit(ERR::WRONG_OP_CODE->value);
        }
        $this->type = $instruction_type;

        // args are rest of words
        $args = array_slice($words, 1);
        $arg_i = 0;
        foreach ($args as $arg) {
            // create symbol from each element
            try {
                // Only first parameter can be label -> check if can be and is first
                $can_be_label = in_array($instruction_type, $allowed_label) && $arg_i == 0;
                $symbol = SymbolFactory::parse_and_create($arg, $can_be_label);
            } catch (Exception $exception) {
                echo $exception->getMessage();
                exit(ERR::SYNTAX_SEMANTICS_ERR->value);
            }

            $this->args[] = $symbol;
            $arg_i++;
        }
    }

    private function break_line_to_words(string $line): array
    {
        // break instruction into words
        $words = [];

        $word = strtok($line, ' ');
        while ($word != FALSE) {
            $words[] = $word;
            $word = strtok(' ');
        }
        return $words;
    }

    /**
     * @param string $first_word
     * @return INSTR_TYPE
     */
    private function what_instruction_type(string $first_word): ?INSTR_TYPE
    {
        return INSTR_TYPE::tryFrom(strtolower($first_word));
    }

    public function get_type()
    {
        return $this->type;
    }

    /**
     * @return Symbol[]
     */
    public function get_args(): array
    {
        return $this->args;
    }

    public function to_xml(): string
    {
        $xml_str = "";

        for ($i = 0; $i < count($this->args); $i++) {
            $xml_str .= sprintf('<arg%s type="%s">%s</arg%s>',
                $i + 1,
                mb_strtolower($this->args[$i]->get_type()->name),
                $this->args[$i]->to_xml(),
                $i + 1);
        }

        return sprintf('<instruction order="%s" opcode="%s">%s</instruction>', $this->order, mb_strtoupper($this->type->name), $xml_str);
    }
}