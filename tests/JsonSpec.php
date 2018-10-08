<?php

namespace tests\Vcn\Pipette;

use PhpSpec\ObjectBehavior;
use stdClass;
use Vcn\Pipette\Json\Exception;

class JsonSpec extends ObjectBehavior
{
    /**
     * @test
     * @throws Exception\CantDecode
     */
    public function it_should_parse_null()
    {
        $nulls = [
            "null",
            " null",
            "\tnull",
            "\nnull",
            "null ",
            "null\t",
            "null\n",
        ];

        foreach ($nulls as $null) {
            $this::parse($null)->isNull()->shouldBe(true);
        }
    }

    /**
     * @test
     * @throws Exception\CantDecode
     */
    public function it_should_parse_some_json()
    {
        $json =
            '
              {
                "+": {
                  "a": {
                    "var": 6
                  },
                  "b": {
                    "+": {
                      "a": {
                        "var": 2
                      },
                      "b": {
                        "var": 9
                      }
                    }
                  }
                }
              }
          ';

        $this::parse($json)->isObject()->shouldBe(true);
    }

    /**
     * @test
     */
    public function it_should_not_parse_invalid_json()
    {
        $json =
            '
              {
                "+": {
                  "a": {
                    "var": 6
                  },
                  "b": {
                    "+": {
                      "a": {
                        "var": 2
                      },
                      "b": {
                        "var": 9
                      }
                    }
                  }
                }

          ';

        json_decode($json);

        $e = new Exception\CantDecode(json_last_error_msg(), json_last_error());

        $this->shouldThrow($e)->during('parse', [$json]);
    }

    /**
     * @test
     */
    public function it_should_pretty_print_json_values()
    {
        $this::prettyPrintType("chocolate flakes")->shouldBe("string");
        $this::prettyPrintType(true)->shouldBe("true");
        $this::prettyPrintType(false)->shouldBe("false");
        $this::prettyPrintType(null)->shouldBe("null");
        $this::prettyPrintType(new stdClass())->shouldBe("object");
        $this::prettyPrintType([1, 2, 3])->shouldBe("array");
        $this::prettyPrintType(1)->shouldBe("number");
        $this::prettyPrintType(3.6)->shouldBe("number");
    }

    /**
     * @test
     */
    public function it_should_pretty_print_non_json_values_as_unknown_type()
    {
        $f = function () {
        };

        $this::prettyPrintType($f)->shouldBe('unknown type');
        $this::prettyPrintType(fopen('php://memory', 'w'))->shouldBe('unknown type');
    }
}
