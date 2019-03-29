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

namespace oat\taoResultServer\models\classes\OutcomeFileSystemStorage;


class FilePathFactory
{
    /**
     * @param string $deliveryResultIdentifier
     *
     * @return string
     */
    public function getFilePath($deliveryResultIdentifier)
    {
        return sprintf(
            '%s/%s',
            $this->getDirPath($deliveryResultIdentifier),
            $this->buildIdentifier()
        );
    }

    /**
     * @param string $deliveryResultIdentifier
     *
     * @return string
     */
    public function getDirPath($deliveryResultIdentifier)
    {
        $path = md5($deliveryResultIdentifier);

        $slashPosition = 1;
        for ($i = 1; $i < 4; $i++) {
            $path = substr_replace($path, '/', $slashPosition, 0);
            $slashPosition += 2;
        }

        return $path;
    }

    private function buildIdentifier()
    {
        return md5(uniqid('', true) . mt_rand(0, 10000));
    }
}
