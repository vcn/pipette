<?php

namespace tests\Vcn\Pipette\Json\Exception;

use PhpSpec\ObjectBehavior;
use Vcn\Pipette\Json\Exception;

class ManyAssertionsFailedSpec extends ObjectBehavior
{
    /**
     * @test
     */
    public function it_should_correctly_format_a_flat_tree()
    {
        $e1 = new Exception\AssertionFailed("This is failure 1.");
        $e2 = new Exception\AssertionFailed("This is failure 2.");

        $this->beConstructedWith('', 0, null, $e2, $e1, $e2);

        $this::fromFailedAssertions($e1, $e2)->getMessage()->shouldBe(
            "Expected any of the following:\n" .
            "    - This is failure 1.\n" .
            "    - This is failure 2."
        );
    }

    /**
     * @test
     */
    public function it_should_correctly_format_a_nested_tree()
    {
        $e1 = new Exception\AssertionFailed("This is failure 1.");
        $e2 = new Exception\AssertionFailed("This is failure 2.");
        $e3 = new Exception\AssertionFailed("This is failure 3.");

        $e1and2 = Exception\ManyAssertionsFailed::fromFailedAssertions($e1, $e2);

        $this->beConstructedWith('', 0, null, $e2, $e1, $e2);

        $this::fromFailedAssertions($e3, $e1and2)->getMessage()->shouldBe(
            "Expected any of the following:\n" .
            "    - This is failure 3.\n" .
            "    - Expected any of the following:\n" .
            "        - This is failure 1.\n" .
            "        - This is failure 2."
        );
    }

    /**
     * @test
     */
    public function it_should_sort_messages_on_whether_they_have_children()
    {
        $e1 = new Exception\AssertionFailed("This is failure 1.");
        $e2 = new Exception\AssertionFailed("This is failure 2.");
        $e3 = new Exception\AssertionFailed("This is failure 3.");

        $e1and2 = Exception\ManyAssertionsFailed::fromFailedAssertions($e1, $e2);

        $this->beConstructedWith('', 0, null, $e2, $e1, $e2);

        $this::fromFailedAssertions($e1and2, $e3)->getMessage()->shouldBe(
            "Expected any of the following:\n" .
            "    - This is failure 3.\n" .
            "    - Expected any of the following:\n" .
            "        - This is failure 1.\n" .
            "        - This is failure 2."
        );
    }

    /**
     * @test
     */
    public function it_should_give_the_failed_assertions_as_an_array()
    {
        $e1 = new Exception\AssertionFailed("This is failure 1.");
        $e2 = new Exception\AssertionFailed("This is failure 2.");

        $this->beConstructedWith('', 0, null, $e2, $e1, $e2);

        $this::fromFailedAssertions($e1, $e2)->getFailedAssertions()->shouldBe([$e1, $e2]);
    }

    /**
     * @test
     */
    public function it_should_uncons_the_failed_assertions()
    {
        $e1 = new Exception\AssertionFailed("This is failure 1.");
        $e2 = new Exception\AssertionFailed("This is failure 2.");

        $this->beConstructedWith('', 0, null, $e2, $e1, $e2);

        $this::fromFailedAssertions($e1, $e2)->unconsFailedAssertions()->shouldBe([$e1, [$e2]]);
    }
}
