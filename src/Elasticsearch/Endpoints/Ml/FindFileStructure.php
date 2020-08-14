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

use Elasticsearch\Common\Exceptions\InvalidArgumentException;
use Elasticsearch\Endpoints\AbstractEndpoint;
use Elasticsearch\Serializers\SerializerInterface;
use Traversable;

/**
 * Class FindFileStructure
 * Elasticsearch API name ml.find_file_structure
 * Generated running $ php util/GenerateEndpoints.php 7.9
 *
 */
class FindFileStructure extends AbstractEndpoint
{

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getURI(): string
    {

        return "/_ml/find_file_structure";
    }

    public function getParamWhitelist(): array
    {
        return [
            'lines_to_sample',
            'line_merge_size_limit',
            'timeout',
            'charset',
            'format',
            'has_header_row',
            'column_names',
            'delimiter',
            'quote',
            'should_trim_fields',
            'grok_pattern',
            'timestamp_field',
            'timestamp_format',
            'explain'
        ];
    }

    public function getMethod(): string
    {
        return 'POST';
    }
    
    public function setBody($body): FindFileStructure
    {
        if (isset($body) !== true) {
            return $this;
        }
        if (is_array($body) === true || $body instanceof Traversable) {
            foreach ($body as $item) {
                $this->body .= $this->serializer->serialize($item) . "\n";
            }
        } elseif (is_string($body)) {
            $this->body = $body;
            if (substr($body, -1) != "\n") {
                $this->body .= "\n";
            }
        } else {
            throw new InvalidArgumentException("Body must be an array, traversable object or string");
        }
        return $this;
    }
}
