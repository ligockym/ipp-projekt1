<?php

class InstructionFormat
{
    private INSTR_TYPE $type;
    private array $allowed_arg_types;
    private Closure $condition;

    /**
     * @param INSTR_TYPE $type
     * @param DATA_TYPE[] $allowed_arg1_types
     * @param DATA_TYPE[] $allowed_arg2_types
     * @param DATA_TYPE[] $allowed_arg3_types
     * @param Closure $condition
     */
    public function __construct(INSTR_TYPE $type, array $allowed_arg1_types = [], array $allowed_arg2_types = [], array $allowed_arg3_types = [], Closure $condition = null)
    {
        $this->type = $type;
        $this->allowed_arg_types[] = $allowed_arg1_types;
        $this->allowed_arg_types[] = $allowed_arg2_types;
        $this->allowed_arg_types[] = $allowed_arg3_types;
        $this->condition = $condition ?? function() {
            return;
        };
    }

    /**
     * @return INSTR_TYPE
     */
    public function get_type(): INSTR_TYPE
    {
        return $this->type;
    }

    /**
     * Indexing from 0
     * @return DATA_TYPE[]
     */
    public function get_allowed_arg_types(int $arg_position): array
    {
        return $this->allowed_arg_types[$arg_position] ?? [];
    }

    /**
     * Returns number of arguments for instruction.
     * @return int
     */
    public function get_allowed_arg_count(): int {
        $non_empty = 0;
        // find non empty array
        foreach ($this->allowed_arg_types as $type) {
            if (count($type)) {
                $non_empty++;
            }
        }
        return $non_empty;
    }

    /**
     * @return Closure
     */
    public function get_condition(): Closure
    {
        return $this->condition;
    }

    public function first_arg_can_be_label(): bool {
        if (isset($this->allowed_arg_types[0]) && in_array(DATA_TYPE::LABEL, $this->allowed_arg_types[0])) {
            return true;
        }
        return false;
    }
}