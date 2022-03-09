<?php

class Program
{
    /**
     * @var Instruction[]
     * used to represent real instruction written from input, so contains value of arguments
     */
    private array $instructions = [];

    private bool $was_header = false;

    /**
     * @var InstructionFormat[]
     * used to represent possible format of instruction,
     * contains only possible types for arguments
     */
    private array $instruction_formats;

    /**
     * @var INSTR_TYPE[]
     */
    private array $instructions_allowed_label;

    public function __construct()
    {
        $this->fill_instruction_formats();
    }

    /**
     * Returns XML of whole program
     * @return string
     */
    public function to_xml(): string {
        $xml_str = "";

        // add instructions
        foreach ($this->instructions as $instruction) {
            $xml_str .=  $instruction->to_xml();
        }

        $xml = new SimpleXMLElement('<program language="IPPcode22">' . $xml_str . '</program>');

        return $xml->asXML();
    }

    public function parse_line(string $line)
    {
        $line_no_comment = preg_replace("/#.*/", '', $line);
        $trimmed_line = trim($line_no_comment);

        if (!$trimmed_line) {
            // empty line
            return;
        }

        // check if header on first line, if not, exit error, if yes, then skip line
        if (!$this->was_header) {
            if ($trimmed_line == '.IPPcode22') {
                $this->was_header = true;
                return;
            } else {
                // something else then header -> error
                echo "Error wrong header\n";
                exit(ERR::WRONG_HEADER->value);
            }
        }

        $instruction = new Instruction($trimmed_line, $this->instructions_allowed_label, count($this->instructions) + 1);
        $format = $this->find_instruction_format($instruction->get_type());

        try {
            InstructionValidator::validate($instruction, $format);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            exit(ERR::SYNTAX_SEMANTICS_ERR->value);
        }
        $this->instructions[] = $instruction;
    }

    /**
     * Finds corresponding format for an instruction type
     * @param INSTR_TYPE $type
     * @return InstructionFormat|null
     */
    private function find_instruction_format(INSTR_TYPE $type): ?InstructionFormat {
        foreach ($this->instruction_formats as $format) {
            if ($format->get_type() == $type) {
                return $format;
            }
        }
        return null;
    }

    // TODO: Fill instruction formats should be accessible from Instruction object so we could just use instruction.validate_with_format() and it would validate itself with tits format
    private function fill_instruction_formats()
    {
        // Including DATA_TYPE::VAR, because it can be userd anywhere except <label> and <type>
        $datable_types = [DATA_TYPE::BOOL, DATA_TYPE::INT, DATA_TYPE::STRING];
        $datable_types_nil = [DATA_TYPE::BOOL, DATA_TYPE::INT, DATA_TYPE::NIL, DATA_TYPE::STRING];

        $second_third_same = function(Instruction $instruction) {
            $args = $instruction->get_args();
            $arg1 = $args[1]->get_type();
            $arg2 = $args[2]->get_type();
            return $arg1 == $arg2 || ($arg1 == DATA_TYPE::VAR) || ($arg2 == DATA_TYPE::VAR);
        };

        $this->instruction_formats = [
            new InstructionFormat(INSTR_TYPE::MOVE, [DATA_TYPE::VAR], $datable_types_nil),
            new InstructionFormat(INSTR_TYPE::CREATEFRAME),
            new InstructionFormat(INSTR_TYPE::PUSHFRAME),
            new InstructionFormat(INSTR_TYPE::POPFRAME),
            new InstructionFormat(INSTR_TYPE::DEFVAR, [DATA_TYPE::VAR]),
            new InstructionFormat(INSTR_TYPE::CALL, [DATA_TYPE::LABEL]),
            new InstructionFormat(INSTR_TYPE::RETURN),
            new InstructionFormat(INSTR_TYPE::PUSHS, $datable_types_nil),
            new InstructionFormat(INSTR_TYPE::POPS, [DATA_TYPE::VAR]),
            new InstructionFormat(INSTR_TYPE::ADD, [DATA_TYPE::VAR], [DATA_TYPE::INT], [DATA_TYPE::INT]),
            new InstructionFormat(INSTR_TYPE::SUB, [DATA_TYPE::VAR], [DATA_TYPE::INT], [DATA_TYPE::INT]),
            new InstructionFormat(INSTR_TYPE::MUL, [DATA_TYPE::VAR], [DATA_TYPE::INT], [DATA_TYPE::INT]),
            new InstructionFormat(INSTR_TYPE::IDIV, [DATA_TYPE::VAR], [DATA_TYPE::INT], [DATA_TYPE::INT]),
            new InstructionFormat(INSTR_TYPE::LT, [DATA_TYPE::VAR], $datable_types, $datable_types, $second_third_same),
            new InstructionFormat(INSTR_TYPE::GT, [DATA_TYPE::VAR], $datable_types, $datable_types, $second_third_same),
            new InstructionFormat(INSTR_TYPE::EQ, [DATA_TYPE::VAR], $datable_types_nil, $datable_types_nil, $second_third_same),
            new InstructionFormat(INSTR_TYPE::AND, [DATA_TYPE::VAR], [DATA_TYPE::BOOL], [DATA_TYPE::BOOL]),
            new InstructionFormat(INSTR_TYPE::OR, [DATA_TYPE::VAR], [DATA_TYPE::BOOL], [DATA_TYPE::BOOL]),
            new InstructionFormat(INSTR_TYPE::NOT, [DATA_TYPE::VAR], [DATA_TYPE::BOOL], [DATA_TYPE::BOOL], $second_third_same),
            new InstructionFormat(INSTR_TYPE::INT2CHAR, [DATA_TYPE::VAR], [DATA_TYPE::INT]),
            new InstructionFormat(INSTR_TYPE::STRI2INT, [DATA_TYPE::VAR], [DATA_TYPE::STRING], [DATA_TYPE::INT]),
            new InstructionFormat(INSTR_TYPE::READ, [DATA_TYPE::VAR], [DATA_TYPE::TYPE]),
            new InstructionFormat(INSTR_TYPE::WRITE, $datable_types_nil),
            new InstructionFormat(INSTR_TYPE::CONCAT, [DATA_TYPE::VAR], [DATA_TYPE::STRING], [DATA_TYPE::STRING]),
            new InstructionFormat(INSTR_TYPE::STRLEN, [DATA_TYPE::VAR], [DATA_TYPE::STRING]),
            new InstructionFormat(INSTR_TYPE::GETCHAR, [DATA_TYPE::VAR], [DATA_TYPE::STRING], [DATA_TYPE::INT]),
            new InstructionFormat(INSTR_TYPE::SETCHAR, [DATA_TYPE::VAR], [DATA_TYPE::INT], [DATA_TYPE::STRING]),
            new InstructionFormat(INSTR_TYPE::TYPE, [DATA_TYPE::VAR], $datable_types_nil),
            new InstructionFormat(INSTR_TYPE::LABEL, [DATA_TYPE::LABEL]),
            new InstructionFormat(INSTR_TYPE::JUMP, [DATA_TYPE::LABEL]),
            new InstructionFormat(INSTR_TYPE::JUMPIFEQ, [DATA_TYPE::LABEL], $datable_types_nil, $datable_types_nil),
            new InstructionFormat(INSTR_TYPE::JUMPIFNEQ, [DATA_TYPE::LABEL], $datable_types_nil, $datable_types_nil),
            new InstructionFormat(INSTR_TYPE::EXIT, [DATA_TYPE::INT]),
            new InstructionFormat(INSTR_TYPE::DPRINT, $datable_types_nil),
            new InstructionFormat(INSTR_TYPE::BREAK),
        ];

        // All instructions where the first argument can be label (LABEL, JUMP, JUMPIFEQ...)
        $this->instructions_allowed_label = array_map(function($format) {
            return $format->get_type();
        }, array_filter($this->instruction_formats, function($format) {
            return $format->first_arg_can_be_label();
        }));
    }


}