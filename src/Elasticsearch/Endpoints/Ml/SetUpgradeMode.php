<?php
/**
 * Elasticsearch PHP client
 *
 * @link      https://github.com/elastic/elasticsearch-php/
 * @copyright Copyright (c) Elasticsearch B.V (https://www.elastic.co)
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license   https://www.gnu.org/licenses/lgpl-2.1.html GNU Lesser General Public License, Version 2.1
 *
 * Licensed to Elasticsearch B.V under one or more agreements.
 * Elasticsearch B.V licenses this file to you under the Apache 2.0 License or
 * the GNU Lesser General Public License, Version 2.1, at your option.
 * See the LICENSE file in the project root for more information.
 */

declare(strict_types = 1);

namespace Elasticsearch\Endpoints\Ml;

use Elasticsearch\Endpoints\AbstractEndpoint;

/**
 * Class SetUpgradeMode
 * Elasticsearch API name ml.set_upgrade_mode
 * Generated running $ php util/GenerateEndpoints.php 7.9
 *
 */
class SetUpgradeMode extends AbstractEndpoint
{

    public function getURI(): string
    {

        return "/_ml/set_upgrade_mode";
    }

    public function getParamWhitelist(): array
    {
        return [
            'enabled',
            'timeout'
        ];
    }

    public function getMethod(): string
    {
        return 'POST';
    }
}
