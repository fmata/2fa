<?php

declare(strict_types=1);

namespace Scheb\TwoFactorBundle\Tests\Security\Authorization\Voter;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenInterface;
use Scheb\TwoFactorBundle\Security\Authorization\Voter\TwoFactorInProgressVoter;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TwoFactorInProgressVoterTest extends TestCase
{
    private TwoFactorInProgressVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new TwoFactorInProgressVoter();
    }

    /**
     * @test
     */
    public function vote_isNotTwoFactorToken_returnAbstain(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $returnValue = $this->voter->vote($token, null, [AuthenticatedVoter::PUBLIC_ACCESS]);
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $returnValue);
    }

    /**
     * @test
     * @dataProvider provideAttributeAndExpectedResult
     */
    public function vote_isTwoFactorToken_returnAbstain(?string $checkAttribute, int $expectedResult): void
    {
        $token = $this->createMock(TwoFactorTokenInterface::class);
        $returnValue = $this->voter->vote($token, null, [$checkAttribute]);
        $this->assertEquals($expectedResult, $returnValue);
    }

    /**
     * @return array<array<mixed>>
     */
    public function provideAttributeAndExpectedResult(): array
    {
        return [
            // Abstain
            [null, VoterInterface::ACCESS_ABSTAIN],
            ['any', VoterInterface::ACCESS_ABSTAIN],
            [AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED, VoterInterface::ACCESS_ABSTAIN],
            [AuthenticatedVoter::IS_AUTHENTICATED_FULLY, VoterInterface::ACCESS_ABSTAIN],

            // Granted
            [AuthenticatedVoter::PUBLIC_ACCESS, VoterInterface::ACCESS_GRANTED],
            [TwoFactorInProgressVoter::IS_AUTHENTICATED_2FA_IN_PROGRESS, VoterInterface::ACCESS_GRANTED],
        ];
    }
}
