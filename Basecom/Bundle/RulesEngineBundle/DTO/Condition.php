<?php

namespace Basecom\Bundle\RulesEngineBundle\DTO;

use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * @author Peter van der Zwaag <vanderzwaag@basecom.de>
 */
class Condition
{
    const OPERATORS = [
        'starts_with'        => 'STARTS WITH',
        'ends_with'          => 'ENDS WITH',
        'contains'           => 'CONTAINS',
        'does_not_contain'   => 'DOES NOT CONTAIN',
        'equal'              => '=',
        'not_equal'          => '!=',
        'empty'              => 'EMPTY',
        'not_empty'          => 'NOT EMPTY',
        'between'            => 'BETWEEN',
        'not_between'        => 'NOT BETWEEN',
        'in'                 => 'IN',
        'not_in'             => 'NOT IN',
        'unclassified'       => 'UNCLASSIFIED',
        'in_or_unclassified' => 'IN OR UNCLASSIFIED',
        'in_children'        => 'IN CHILDREN',
        'not_in_children'    => 'NOT IN CHILDREN',
        'greater'            => '>',
        'greater_or_equal'   => '>=',
        'smaller'            => '<',
        'smaller_or_equal'   => '<=',
    ];

    const MULTIPLE_VALUE_OPERATORS = [
        'between'     => 'BETWEEN',
        'not_between' => 'NOT BETWEEN',
        'in'          => 'IN',
        'not_in'      => 'NOT IN',
    ];

    /**
     * @var Attribute|string
     */
    public $field;

    /**
     * @var string
     */
    public $operator;

    /**
     * @var string[]
     */
    public $values;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var string
     */
    public $scope;

    /**
     * @var string
     */
    public $unit;

    /**
     * @return array
     */
    public function getData(): array
    {
        $data['field'] = $this->field instanceof Attribute ? $this->field->getCode() : $this->field;

        if (array_key_exists($this->operator, self::OPERATORS)) {
            $data['operator'] = self::OPERATORS[$this->operator];
        }

        if ('' !== $this->scope && (!$this->field instanceof Attribute || $this->field->isScopable())) {
            $data['scope'] = $this->scope;
        }
        if ('' !== $this->locale && (!$this->field instanceof Attribute || $this->field->isLocalizable())) {
            $data['locale'] = $this->locale;
        }

        $data['value'] = reset($this->values);

        if ($this->field instanceof Attribute) {
            if (null !== $this->field->getMetricFamily() && 0 < strlen($this->field->getMetricFamily())) {
                $data['value']           = [];
                $data['value']['amount'] = reset($this->values);
                $data['value']['unit']   = $this->unit;
            }
        } elseif (null !== $this->unit && 0 < strlen($this->unit) && 1 === count($this->values)) {
            $data['value']           = [];
            $data['value']['amount'] = reset($this->values);
            $data['value']['unit']   = $this->unit;
        }

        if (in_array(strtoupper($this->operator), self::MULTIPLE_VALUE_OPERATORS, true)) {
            $data['value'] = $this->values;
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return Condition
     */
    public static function fromData(array $data): self
    {
        $condition        = new self();
        $condition->field = $data['field'];

        if (in_array($data['operator'], self::OPERATORS, true)) {
            $condition->operator = array_keys(self::OPERATORS, $data['operator'], true)[0];
        }

        if (array_key_exists('value', $data)) {
            if (is_array($data['value'])) {
                if (array_key_exists('amount', $data['value'])) {
                    $condition->values[] = $data['value']['amount'];
                    $condition->unit     = $data['value']['unit'];
                } else {
                    $condition->values = $data['value'];
                }
            } else {
                $condition->values[] = $data['value'];
            }
        }

        if (array_key_exists('locale', $data)) {
            $condition->locale = $data['locale'];
        }
        if (array_key_exists('scope', $data)) {
            $condition->scope = $data['scope'];
        }

        return $condition;
    }
}
