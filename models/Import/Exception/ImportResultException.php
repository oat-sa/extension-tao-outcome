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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoResultServer\models\Import\Exception;

use common_Exception;
use common_exception_UserReadableException;
use Throwable;

class ImportResultException extends common_Exception implements common_exception_UserReadableException
{
    private string $userMessage;

    public function __construct(string $userMessage, $internalMessage = null, Throwable $previous = null)
    {
        $this->userMessage = $userMessage;

        parent::__construct($internalMessage ?? $userMessage, 0, $previous);
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }
}
