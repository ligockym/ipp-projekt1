<?php

/**
 * Represents parser, it saves all instructions loaded and all possible instruction formats.
 */
class Program
{
    /**
     * @var Instruction[]
     * used to represent real instruction written from input, so contains value of arguments
     */
    private array $instructions = [];

    /**
     * Determines whether header was loaded.
     * @var bool
     */
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

    /**
     * @param InstructionFormat[] $instruction_formats all possible instruction formats
     */
    public function __construct(array $instruction_formats)
    {
        $this->instruction_formats = $instruction_formats;

        // All instructions where the first argument can be label (LABEL, JUMP, JUMPIFEQ...)
        $this->instructions_allowed_label = array_map(function($format) {
            return $format->get_type();
        }, array_filter($this->instruction_formats, function($format) {
            return $format->first_arg_can_be_label();
        }));
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

    /**
     * Removes comments, trim white characters, check header, create Instruction and validate.
     * @param string $line
     */
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

        // create new instruction, its constructor will create symbols
        $instruction = new Instruction($trimmed_line, $this->instructions_allowed_label, count($this->instructions) + 1);
        $format = $this->find_instruction_format($instruction->get_type());

        // validate instruction
        try {
            InstructionValidator::validate($instruction, $format);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            exit(ERR::SYNTAX_SEMANTICS_ERR->value);
        }

        // instruction is valid
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

}