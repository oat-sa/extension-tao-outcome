<?php

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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoResultServer\models\Parser;

use LogicException;
use oat\oatbox\service\ConfigurableService;
use oat\taoResultServer\models\Mapper\ResultMapper;
use qtism\data\results\AssessmentResult;
use qtism\data\storage\xml\XmlResultDocument;
use qtism\data\storage\xml\XmlStorageException;

class QtiResultParser extends ConfigurableService
{
    /**
     * Parse an xml to provide a map
     *
     * @param string $xml
     * @return ResultMapper
     * @throws XmlStorageException
     */
    public function parse($xml)
    {
        if (!is_string($xml)) {
            throw new LogicException('Qti Result parser expects a string as data source.');
        }

        $doc = new XmlResultDocument();
        $doc->loadFromString($xml, true);

        /** @var AssessmentResult $assessmentResult */
        return $this->getMapper()->loadSource($doc->getDocumentComponent());
    }

    /**
     * Get service to map result object form xml
     *
     * @return ResultMapper
     */
    protected function getMapper()
    {
        return $this->getServiceLocator()->get(ResultMapper::class);
    }
}
