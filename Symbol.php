<?php

/**
 * Symbol is representation of each argument in instruction. It can be either Constant, Variable, Type or Label.
 */
interface Symbol
{
    /**
     * Returns whether $data_type is allowed type for a symbol.
     * eg. for string only string or variable are valid
     * @param DATA_TYPE $data_type
     * @return bool
     */
    public function can_be_used_as(DATA_TYPE $data_type): bool;
    public function to_xml(): string;
    public function get_type(): DATA_TYPE;
}