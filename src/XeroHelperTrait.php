<?php

namespace Radcliffe\Xero;

trait XeroHelperTrait
{
    /**
     * Valid condition operators.
     *
     * @var array
     */
    protected static $conditionOperators = ['==', '!=', 'StartsWith', 'EndsWith', 'Contains', 'guid'];

    /**
     * The Xero API conditions for GET requests.
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * Get the conditions for the request.
     *
     * @return array
     *   The conditions protected property.
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Add a condition to the request.
     *
     * @param string $field
     *   The field to add the condition for.
     * @param string $value
     *   The value to compare against.
     * @param string $operator
     *   The operator to use in the condition:
     *      - ==: Equal to the value.
     *      - !=: Not equal to the value.
     *      - StartsWith: Starts with the value.
     *      - EndsWith: Ends with the value.
     *      - Contains: Contains the value.
     *      - guid: Equality for guid values. See Xero API.
     *
     * @return $this
     */
    public function addCondition($field, $value = '', $operator = '==')
    {
        if (!in_array($operator, self::$conditionOperators)) {
            throw new \InvalidArgumentException('Invalid operator');
        }

        // Transform a boolean value to its string representation.
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        // Construct condition statement based on operator.
        if (in_array($operator, ['==', '!='])) {
            $this->conditions[] = $field . $operator . '"' . $value . '"';
        } elseif ($operator === 'guid') {
            $this->conditions[] = $field . '= Guid("'. $value . '")';
        } else {
            $this->conditions[] = $field . '.' . $operator . '("' . $value . '")';
        }

        return $this;
    }

    /**
     * Adds a logical operator AND or OR to the conditions array.
     *
     * @param string $operator
     *   The operator, either AND or OR.
     *
     * @return $this
     */
    public function addOperator($operator = 'AND')
    {
        if (!in_array($operator, ['AND', 'OR'])) {
            throw new \InvalidArgumentException('Invalid logical operator');
        }

        $this->conditions[] = $operator;

        return $this;
    }

    /**
     * Compile the conditions array into a query parameter.
     *
     * @return array
     *   An associative array that can be merged into the query options.
     */
    public function compileConditions()
    {
        $ret = [];
        if (!empty($this->conditions)) {
            $ret['where'] = implode(' ', $this->conditions);
        }
        return $ret;
    }

    /**
     * Get the order query parameter based on the arguments.
     *
     * @param string $field
     *   The field to order by.
     * @param string $direction
     *   An optional direction.
     *
     * @return array
     *   An associative array that can be merged into the query options.
     */
    public function orderBy($field, $direction = 'ASC')
    {
        $ret = ['order' => $field];
        if ($direction === 'DESC') {
            $ret['order'] .= ' ' . $direction;
        }
        return $ret;
    }

    /**
     * Parse the Authorization HTTP Request parameters.
     *
     * @param string $request_parameters
     *  The HTTP Request parameters from the API to the web server.
     *
     * @return array
     *  An associative array keyed by the parameter key.
     */
    public function getRequestParameters($request_parameters)
    {
        $ret = [];
        $parts = explode('&', $request_parameters);
        foreach ($parts as $part) {
            list($key, $value) = explode('=', $part);
            $key_decoded = urldecode($key);
            $value_decoded = urldecode($value);
            $ret[$key_decoded] = $value_decoded;
        }
        return $ret;
    }
}
