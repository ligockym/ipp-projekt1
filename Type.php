<?php

class Type implements Symbol
{
    private DATA_TYPE $type;

    /**
     * @param DATA_TYPE $type
     */
    public function __construct(DATA_TYPE $type)
    {
        $this->type = $type;
    }


    public function can_be_used_as(DATA_TYPE $type): bool
    {
        return $type == DATA_TYPE::TYPE;
    }

    public function to_xml(): string
    {
        return $this->type->value;
    }

    public function get_type(): DATA_TYPE
    {
        return DATA_TYPE::TYPE;
    }
}