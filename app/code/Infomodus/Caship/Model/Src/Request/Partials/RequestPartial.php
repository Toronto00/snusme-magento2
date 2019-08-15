<?php
namespace Infomodus\Caship\Model\Src\Request\Partials;
class RequestPartial
{
    protected $required = array();

    public function toArray()
    {
        return $this->convertToArray($this->required);
    }

    private function convertToArray($data)
    {
        $result = array();
        foreach ($data as $key => $value) {
            if ($value instanceof RequestPartial) {
                $result[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $result[$key] = $this->convertToArray($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
