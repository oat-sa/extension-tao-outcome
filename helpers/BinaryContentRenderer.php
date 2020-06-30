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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoResultServer\helpers;

use finfo;
use oat\oatbox\service\ConfigurableService;

/**
 * This service is intended to render a binary file content to the different formats
 */
class BinaryContentRenderer extends ConfigurableService
{
    /** @var string */
    public const SERVICE_ID = 'taoResultService/BinaryContentRenderer';

    /**
     * BinaryContentRenderer constructor.
     *
     * @param array $options An array of Service options.
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    /**
     * Tries to guess a MIME type from passed binary content and builds a properly formatted string
     * @param string $binaryContent
     * @return string
     */
    public function renderBinaryContentAsVariableValue(string $binaryContent): stringq
    {
        $info = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $info->buffer($binaryContent);

        return sprintf('%s,base64,%s', $mimeType, base64_encode($binaryContent));
    }
}
