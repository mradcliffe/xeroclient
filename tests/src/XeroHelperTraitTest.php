<?php

namespace Radcliffe\Tests\Xero;

class XeroHelperTraitTest extends XeroClientTestBase
{

    /**
     * Assert that request parameters are decoded correctly.
     *
     * @param string $parameters
     *   The parameter string to test.
     * @param array<string,mixed> $expected
     *   The expected output from the method.
     *
     * @dataProvider requestParametersProvider
     */
    public function testGetRequestParameters(string $parameters, array $expected): void
    {
        /* @var $mock \Radcliffe\Xero\XeroHelperTrait */
        $mock = $this->getMockForTrait('\Radcliffe\Xero\XeroHelperTrait');

        $this->assertEquals($expected, $mock->getRequestParameters($parameters));
    }

    /**
     * Assert that conditions are added by the appropriate rules.
     *
     * @param string $field
     *   The field parameter.
     * @param bool|int|string $value
     *   The value parameter.
     * @param string $operator
     *   The operator parameter.
     * @param string[] $expected
     *   The expected value of \Radcliffe\Xero\XeroHelperTrait::$conditions.
     *
     * @dataProvider addConditionProvider
     */
    public function testAddCondition(string $field, bool|int|string $value, string $operator, array $expected): void
    {
        /* @var $mock \Radcliffe\Xero\XeroHelperTrait */
        $mock = $this->getMockForTrait('\Radcliffe\Xero\XeroHelperTrait');

        $mock->addCondition($field, $value, $operator);

        $this->assertEquals($expected, $mock->getConditions());
    }

    /**
     * Assert that logical operator can be added to conditions.
     *
     * @param string $operator
     *   The logical operator
     * @param string[] $expected
     *   The expected conditions array.
     *
     * @dataProvider addOperatorProvider
     */
    public function testAddOperator(string $operator, array $expected): void
    {
        /* @var $mock \Radcliffe\Xero\XeroHelperTrait */
        $mock = $this->getMockForTrait('\Radcliffe\Xero\XeroHelperTrait');

        $mock->addOperator($operator);

        $this->assertEquals($expected, $mock->getConditions());
    }

    /**
     * Assert that conditions can be compiled into a query string.
     *
     * @param string[][] $conditions
     *   An array of conditions.
     * @param string[] $expected
     *   The expected value.
     *
     * @dataProvider compileConditionsProvider
     */
    public function testCompileConditions(array $conditions, array $expected): void
    {
        /* @var $mock \Radcliffe\Xero\XeroHelperTrait */
        $mock = $this->getMockForTrait('\Radcliffe\Xero\XeroHelperTrait');
        foreach ($conditions as $condition) {
            $mock->addCondition($condition[0], $condition[1], $condition[2]);
        }

        $this->assertEquals($expected, $mock->compileConditions());
    }

    /**
     * Assert that orderBy method functions.
     *
     * @param string $direction
     *   The direction to order by.
     * @param string[] $expected
     *   The expected value.
     *
     * @dataProvider orderByProvider
     */
    public function testOrderBy(string $direction, array $expected): void
    {
        /* @var $mock \Radcliffe\Xero\XeroHelperTrait */
        $mock = $this->getMockForTrait('\Radcliffe\Xero\XeroHelperTrait');

        $this->assertEquals($expected, $mock->orderBy('Name', $direction));
    }

    /**
     * Assert that invalid operator throughs an exception.
     */
    public function testInvalidAddCondition(): void
    {
        /* @var $mock \Radcliffe\Xero\XeroHelperTrait */
        $mock = $this->getMockForTrait('\Radcliffe\Xero\XeroHelperTrait');

        $this->expectException(\InvalidArgumentException::class);
        $mock->addCondition('Name', 'Value', '<>');
    }

    /**
     * Assert that exception thrown for invalid logical operator.
     */
    public function testInvalidLogicalOperator(): void
    {
        /* @var $mock \Radcliffe\Xero\XeroHelperTrait */
        $mock = $this->getMockForTrait('\Radcliffe\Xero\XeroHelperTrait');

        $this->expectException(\InvalidArgumentException::class);
        $mock->addOperator('NOT');
    }

    /**
     * Provide parameters and expected values for getRequestParameters.
     *
     * @return array<int,mixed>
     *   An array of test method parameters.
     */
    public function requestParametersProvider(): array
    {
        $test1_expected = [
            'oauth_token' => $this->createRandomString(),
            'oauth_verifier' => $this->createRandomString()
        ];
        $test1_string = 'oauth_token=' . urlencode($test1_expected['oauth_token']) . '&' . 'oauth_verifier=' .
            urlencode($test1_expected['oauth_verifier']);

        return [
            [$test1_string, $test1_expected],
        ];
    }

    /**
     * Provide test values for addCondition test.
     *
     * @return array<int,mixed>
     */
    public function addConditionProvider(): array
    {
        $guid = $this->createGuid();
        return [
            ['Name', 'Test Value', '==', ['Name=="Test Value"']],
            ['Name', 'Test Value', '!=', ['Name!="Test Value"']],
            ['IsSupplier', false, '==', ['IsSupplier=="false"']],
            ['ContactID', $guid, 'guid', ['ContactID= Guid("' . $guid . '")']],
            ['Name', 'Test Value', 'StartsWith', ['Name.StartsWith("Test Value")']],
            ['Name', 'Test Value', 'EndsWith', ['Name.EndsWith("Test Value")']],
        ];
    }

    /**
     * Provide values for testAddOperator().
     *
     * @return array<int,mixed>
     */
    public function addOperatorProvider(): array
    {
        return [['AND', ['AND']], ['OR', ['OR']]];
    }

    /**
     * Provide values for testCompileConditions().
     *
     * @return array<int,mixed>
     */
    public function compileConditionsProvider(): array
    {
        return [
            [[], []],
            [
                [
                    ['Name', 'Test Value', '=='],
                ],
                ['where' => 'Name=="Test Value"'],
            ],
            [
                [
                    ['Name', 'Test Value', '=='],
                    ['Code', '2', 'StartsWith'],
                ],
                ['where' => 'Name=="Test Value" Code.StartsWith("2")'],
            ]
        ];
    }

    /**
     * Provide values for testOrderBy().
     *
     * @return array<int,mixed>
     */
    public function orderByProvider(): array
    {
        return [
            ['ASC', ['order' => 'Name']],
            ['DESC', ['order' => 'Name DESC']]
        ];
    }
}
