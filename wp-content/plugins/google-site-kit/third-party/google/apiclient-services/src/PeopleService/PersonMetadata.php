<?php

/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
namespace Google\Site_Kit_Dependencies\Google\Service\PeopleService;

class PersonMetadata extends \Google\Site_Kit_Dependencies\Google\Collection
{
    protected $collection_key = 'sources';
    public $deleted;
    public $linkedPeopleResourceNames;
    public $objectType;
    public $previousResourceNames;
    protected $sourcesType = \Google\Site_Kit_Dependencies\Google\Service\PeopleService\Source::class;
    protected $sourcesDataType = 'array';
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }
    public function getDeleted()
    {
        return $this->deleted;
    }
    public function setLinkedPeopleResourceNames($linkedPeopleResourceNames)
    {
        $this->linkedPeopleResourceNames = $linkedPeopleResourceNames;
    }
    public function getLinkedPeopleResourceNames()
    {
        return $this->linkedPeopleResourceNames;
    }
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }
    public function getObjectType()
    {
        return $this->objectType;
    }
    public function setPreviousResourceNames($previousResourceNames)
    {
        $this->previousResourceNames = $previousResourceNames;
    }
    public function getPreviousResourceNames()
    {
        return $this->previousResourceNames;
    }
    /**
     * @param Source[]
     */
    public function setSources($sources)
    {
        $this->sources = $sources;
    }
    /**
     * @return Source[]
     */
    public function getSources()
    {
        return $this->sources;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Site_Kit_Dependencies\Google\Service\PeopleService\PersonMetadata::class, 'Google\\Site_Kit_Dependencies\\Google_Service_PeopleService_PersonMetadata');
