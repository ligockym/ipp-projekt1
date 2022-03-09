<?php

/**
 * Label is one of possible argument's type. Possible formats: hello, label123...
 */
class Label implements Symbol
{
    private string $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function can_be_used_as(DATA_TYPE $type): bool
    {
        return $type == DATA_TYPE::LABEL;
    }

    public function to_xml(): string
    {
        return htmlspecialchars($this->name);
    }

    public function get_type(): DATA_TYPE
    {
        return DATA_TYPE::LABEL;
    }
}