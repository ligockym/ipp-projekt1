<?php

class Constant implements Symbol
{
    private DATA_TYPE $type;
    private string $value;


    public function __construct(DATA_TYPE $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function can_be_used_as(DATA_TYPE $data_types): bool
    {
        // Can include only same type
        return $data_types == $this->type;
    }

    public function to_xml(): string
    {
        return htmlspecialchars($this->value);
    }

    public function get_type(): DATA_TYPE
    {
        return $this->type;
    }
}