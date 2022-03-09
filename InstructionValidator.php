<?php

class InstructionValidator
{

    /**
     * @throws Exception
     */
    public static function validate(Instruction $instruction, InstructionFormat $instruction_format): void
    {

        if ($instruction->get_type() !== $instruction_format->get_type()) {
            // types does not match
            throw new Exception("Type of instruction and its format does not match.");
        }

        // Will throw an exception when wrong argument type.
        // check number of arguments
        InstructionValidator::check_arg_count($instruction, $instruction_format);
        // check types of arguments
        InstructionValidator::check_arg_types($instruction, $instruction_format);
    }

    /**
     * @param Instruction $instruction
     * @param InstructionFormat $instruction_format
     * @throws Exception
     */
    private static function check_arg_types(Instruction $instruction, InstructionFormat $instruction_format)
    {
        $instr_args = $instruction->get_args();

        // ADD string@hello int@12 => first check if string@hello can be there, then if int@12 can be there
        foreach ($instr_args as $i => $instr_arg) {
            $allowed_types = $instruction_format->get_allowed_arg_types($i);
            $is_allowed = false;

            // check if any of the types is allowed
            foreach ($allowed_types as $allowed_type) {
                if ($instr_arg->can_be_used_as($allowed_type)) {
                    // this argument can be where it is
                    $is_allowed = true;
                    break;
                }
            }

            if (!$is_allowed) {
                // this argument is not allowed where it is
                throw new Exception("Bad argument type for instruction " . $instruction->get_type()->name);
            }
        }
    }

    /**
     * @throws Exception
     */
    private static function check_arg_count(Instruction $instruction, InstructionFormat $instruction_format)
    {
        if (count($instruction->get_args()) !== $instruction_format->get_allowed_arg_count()) {
            throw new Exception("Wrong argument count for instruction " . $instruction->get_type()->name);
        }
    }
}