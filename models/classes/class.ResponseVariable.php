<?php

declare(strict_types=1);

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013 (original work) Open Assessment Technologies S.A.
 *
 * @author "Patrick Plichart, <patrick@taotesting.com>"
 *
 * An Assessment Result is used to report the results of a candidate's interaction
 * with a test and/or one or more items attempted. Information about the test is optional,
 * in some systems it may be possible to interact with items that are not organized into
 * a test at all. For example, items that are organized with learning resources and
 * presented individually in a formative context.
 */
class taoResultServer_models_classes_ResponseVariable extends taoResultServer_models_classes_Variable
{
    public const TYPE = 'responseVariable';

    /**
     * The correct response may be output as part of the report if desired.
     * Systems are not limited to reporting correct responses declared in responseDeclarations. For example, a correct
     * response may be set by a templateRule or may simply have been suppressed from the declaration passed to the
     * delivery engine (e.g., for security).
     *
     * @var mixed|null
     */
    protected $correctResponse;

    /**
     * @var string|null Base64 encoded candidate response
     */
    protected $candidateResponse;

    public function setCorrectResponse($correctResponse): self
    {
        $this->correctResponse = $correctResponse;

        return $this;
    }

    public function getCorrectResponse()
    {
        return $this->correctResponse;
    }

    public function setCandidateResponse($candidateResponse): self
    {
        return $this->setEncodedCandidateResponse(base64_encode((string)$candidateResponse));
    }

    public function setEncodedCandidateResponse($encodedCandidateResponse): self
    {
        $this->candidateResponse = $encodedCandidateResponse;

        return $this;
    }

    /**
     * @return string|false
     */
    public function getCandidateResponse()
    {
        return base64_decode((string)$this->candidateResponse);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getCandidateResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value): self
    {
        $this->setCandidateResponse($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return parent::jsonSerialize() +
            [
                'correctResponse' => $this->correctResponse,
                'candidateResponse' => $this->candidateResponse,
            ];
    }

    protected function getType(): string
    {
        return self::TYPE;
    }
}
