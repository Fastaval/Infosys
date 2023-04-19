<?php

namespace Fv\Tests\Framework;
use PHPUnit\Framework\TestCase;
use RequirementBlob;
use FulfilmentBlob;
use OlderThanRequirement;

class RequirementBlobTest extends TestCase
{
    /**
     * tests plain simple blob with no requirements
     *
     * @access public
     * @return void
     */
    public function testEmptyBlob()
    {
        $blob = new RequirementBlob();

        $f_blob = new FulfilmentBlob();

        $this->assertTrue($blob->isFulFilledBy($f_blob));
    }

    public function testNonFulfilled()
    {
        $blob = new RequirementBlob();

        $age_requirement = new OlderThanRequirement(18);

        $f_blob = new FulfilmentBlob();

        $blob->addRequirement($age_requirement);

        $this->assertFalse($blob->isFulFilledBy($f_blob));
    }

    public function testFulfilled()
    {
        $blob = new RequirementBlob();

        $age_requirement = new OlderThanRequirement(18);

        $f_blob = new FulfilmentBlob();

        $participant = $this->getMockBuilder('Deltagere')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $participant->birthdate = date('Y-m-d', strtotime('now - 19 years'));

        $f_blob->addFulfilment($participant);

        $blob->addRequirement($age_requirement);

        $this->assertTrue($blob->isFulFilledBy($f_blob));
    }
}
