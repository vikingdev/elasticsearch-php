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

namespace Elasticsearch\Endpoints\Transform;

use Elasticsearch\Common\Exceptions\RuntimeException;
use Elasticsearch\Endpoints\AbstractEndpoint;

/**
 * Class StartTransform
 * Elasticsearch API name transform.start_transform
 * Generated running $ php util/GenerateEndpoints.php 7.9
 *
 */
class StartTransform extends AbstractEndpoint
{
    protected $transform_id;

    public function getURI(): string
    {
        $transform_id = $this->transform_id ?? null;

        if (isset($transform_id)) {
            return "/_transform/$transform_id/_start";
        }
        throw new RuntimeException('Missing parameter for the endpoint transform.start_transform');
    }

    public function getParamWhitelist(): array
    {
        return [
            'timeout'
        ];
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function setTransformId($transform_id): StartTransform
    {
        if (isset($transform_id) !== true) {
            return $this;
        }
        $this->transform_id = $transform_id;

        return $this;
    }
}
