<?php

/**
 * Copyright (c) 2015-present, Facebook, Inc. All rights reserved.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace PYS_PRO_GLOBAL\FacebookAds\Object;

use PYS_PRO_GLOBAL\FacebookAds\ApiRequest;
use PYS_PRO_GLOBAL\FacebookAds\Cursor;
use PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface;
use PYS_PRO_GLOBAL\FacebookAds\TypeChecker;
use PYS_PRO_GLOBAL\FacebookAds\Object\Fields\BusinessFields;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultAggregationPeriodValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultBreakdownsValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultMetricsValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultOrderingColumnValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultOrderingTypeValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdStudyTypeValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdsPixelSortByValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessAgreementRequestStatusValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessAssetSharingAgreementRequestStatusValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessPagePermittedTasksValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessPermittedTasksValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessSurveyBusinessTypeValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessTwoFactorTypeValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessUserRoleValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessVerticalValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\CPASCollaborationRequestRequesterAgencyOrBrandValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\ContentDeliveryReportPlatformValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\ContentDeliveryReportPositionValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\CustomConversionCustomEventTypeValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\MeasurementUploadEventAggregationLevelValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\MeasurementUploadEventEventStatusValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\MeasurementUploadEventLookbackWindowValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\MeasurementUploadEventMatchUniverseValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\MeasurementUploadEventTimezoneValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\OracleTransactionTypeValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\ProductCatalogVerticalValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\ProfilePictureSourceBreakingChangeValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\ProfilePictureSourceTypeValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\SystemUserRoleValues;
/**
 * This class is auto-generated.
 *
 * For any issues or feature requests related to this class, please let us know
 * on github and we'll fix in our codegen framework. We'll not be able to accept
 * pull request for this class.
 *
 */
class Business extends \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject
{
    /**
     * @return BusinessFields
     */
    public static function getFieldsEnum()
    {
        return \PYS_PRO_GLOBAL\FacebookAds\Object\Fields\BusinessFields::getInstance();
    }
    protected static function getReferencedEnums()
    {
        $ref_enums = array();
        $ref_enums['TwoFactorType'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessTwoFactorTypeValues::getInstance()->getValues();
        $ref_enums['Vertical'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessVerticalValues::getInstance()->getValues();
        $ref_enums['PermittedTasks'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessPermittedTasksValues::getInstance()->getValues();
        $ref_enums['SurveyBusinessType'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessSurveyBusinessTypeValues::getInstance()->getValues();
        $ref_enums['PagePermittedTasks'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessPagePermittedTasksValues::getInstance()->getValues();
        return $ref_enums;
    }
    public function createAccessToken(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('app_id' => 'string', 'fbe_external_business_id' => 'string', 'scope' => 'list<Permission>', 'system_user_name' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/access_token', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function deleteAdAccounts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('adaccount_id' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_DELETE, '/ad_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getAdStudies(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/ad_studies', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdStudy(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdStudy::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createAdStudy(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('cells' => 'list<Object>', 'client_business' => 'string', 'confidence_level' => 'float', 'cooldown_start_time' => 'int', 'description' => 'string', 'end_time' => 'int', 'name' => 'string', 'objectives' => 'list<Object>', 'observation_end_time' => 'int', 'start_time' => 'int', 'type' => 'type_enum', 'viewers' => 'list<int>');
        $enums = array('type_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdStudyTypeValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/ad_studies', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdStudy(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdStudy::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createAdAccount(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('ad_account_created_from_bm_flag' => 'bool', 'currency' => 'string', 'end_advertiser' => 'Object', 'funding_id' => 'string', 'invoice' => 'bool', 'invoice_group_id' => 'string', 'invoicing_emails' => 'list<string>', 'io' => 'bool', 'media_agency' => 'string', 'name' => 'string', 'partner' => 'string', 'po_number' => 'string', 'timezone_id' => 'unsigned int');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/adaccount', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdAccount(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdAccount::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getAdNetworkAnalytics(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('aggregation_period' => 'aggregation_period_enum', 'breakdowns' => 'list<breakdowns_enum>', 'filters' => 'list<map>', 'limit' => 'unsigned int', 'metrics' => 'list<metrics_enum>', 'ordering_column' => 'ordering_column_enum', 'ordering_type' => 'ordering_type_enum', 'since' => 'datetime', 'until' => 'datetime');
        $enums = array('aggregation_period_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultAggregationPeriodValues::getInstance()->getValues(), 'breakdowns_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultBreakdownsValues::getInstance()->getValues(), 'metrics_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultMetricsValues::getInstance()->getValues(), 'ordering_column_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultOrderingColumnValues::getInstance()->getValues(), 'ordering_type_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultOrderingTypeValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/adnetworkanalytics', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdNetworkAnalyticsSyncQueryResult(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdNetworkAnalyticsSyncQueryResult::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createAdNetworkAnalytic(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('aggregation_period' => 'aggregation_period_enum', 'breakdowns' => 'list<breakdowns_enum>', 'filters' => 'list<Object>', 'limit' => 'int', 'metrics' => 'list<metrics_enum>', 'ordering_column' => 'ordering_column_enum', 'ordering_type' => 'ordering_type_enum', 'since' => 'datetime', 'until' => 'datetime');
        $enums = array('aggregation_period_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultAggregationPeriodValues::getInstance()->getValues(), 'breakdowns_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultBreakdownsValues::getInstance()->getValues(), 'metrics_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultMetricsValues::getInstance()->getValues(), 'ordering_column_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultOrderingColumnValues::getInstance()->getValues(), 'ordering_type_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultOrderingTypeValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/adnetworkanalytics', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getAdNetworkAnalyticsResults(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('query_ids' => 'list<string>');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/adnetworkanalytics_results', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdNetworkAnalyticsAsyncQueryResult(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdNetworkAnalyticsAsyncQueryResult::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getAdsPixels(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('id_filter' => 'string', 'name_filter' => 'string', 'sort_by' => 'sort_by_enum');
        $enums = array('sort_by_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\AdsPixelSortByValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/adspixels', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdsPixel(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdsPixel::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createAdsPixel(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('name' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/adspixels', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdsPixel(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdsPixel::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function deleteAgencies(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('business' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_DELETE, '/agencies', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getAgencies(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/agencies', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createAggregateRevenue(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('ecpms' => 'list<string>', 'query_ids' => 'list<string>', 'request_id' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/aggregate_revenue', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getAnPlacements(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/an_placements', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdPlacement(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdPlacement::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createBlockListDraft(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('publisher_urls_file' => 'file');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/block_list_drafts', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getBusinessAssetGroups(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/business_asset_groups', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAssetGroup(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAssetGroup::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getBusinessInvoices(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('end_date' => 'string', 'invoice_id' => 'unsigned int', 'issue_end_date' => 'string', 'issue_start_date' => 'string', 'root_id' => 'unsigned int', 'start_date' => 'string', 'type' => 'type_enum');
        $enums = array('type_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\OracleTransactionTypeValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/business_invoices', new \PYS_PRO_GLOBAL\FacebookAds\Object\OracleTransaction(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\OracleTransaction::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getBusinessUnits(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/business_units', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessUnit(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessUnit::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createBusinessUnit(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('business_units' => 'list<Object>');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/business_units', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessUnit(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessUnit::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getBusinessUsers(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/business_users', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessUser(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessUser::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createBusinessUser(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('email' => 'string', 'role' => 'role_enum');
        $enums = array('role_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessUserRoleValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/business_users', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessUser(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessUser::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createClaimCustomConversion(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('custom_conversion_id' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/claim_custom_conversions', new \PYS_PRO_GLOBAL\FacebookAds\Object\CustomConversion(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\CustomConversion::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getClientAdAccounts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/client_ad_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdAccount(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdAccount::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createClientAdAccount(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('adaccount_id' => 'string', 'permitted_tasks' => 'list<permitted_tasks_enum>');
        $enums = array('permitted_tasks_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessPermittedTasksValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/client_ad_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getClientApps(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/client_apps', new \PYS_PRO_GLOBAL\FacebookAds\Object\Application(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Application::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createClientApp(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('app_id' => 'Object');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/client_apps', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getClientPages(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/client_pages', new \PYS_PRO_GLOBAL\FacebookAds\Object\Page(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Page::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createClientPage(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('page_id' => 'int', 'permitted_tasks' => 'list<permitted_tasks_enum>');
        $enums = array('permitted_tasks_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessPermittedTasksValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/client_pages', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getClientPixels(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/client_pixels', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdsPixel(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdsPixel::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getClientProductCatalogs(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/client_product_catalogs', new \PYS_PRO_GLOBAL\FacebookAds\Object\ProductCatalog(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\ProductCatalog::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getClientWhatsAppBusinessAccounts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/client_whatsapp_business_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\WhatsAppBusinessAccount(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\WhatsAppBusinessAccount::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function deleteClients(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('business' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_DELETE, '/clients', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getClients(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/clients', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getCollaborativeAdsCollaborationRequests(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('status' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/collaborative_ads_collaboration_requests', new \PYS_PRO_GLOBAL\FacebookAds\Object\CPASCollaborationRequest(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\CPASCollaborationRequest::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createCollaborativeAdsCollaborationRequest(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('brands' => 'list<string>', 'contact_email' => 'string', 'contact_first_name' => 'string', 'contact_last_name' => 'string', 'phone_number' => 'string', 'receiver_business' => 'string', 'requester_agency_or_brand' => 'requester_agency_or_brand_enum', 'sender_client_business' => 'string');
        $enums = array('requester_agency_or_brand_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\CPASCollaborationRequestRequesterAgencyOrBrandValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/collaborative_ads_collaboration_requests', new \PYS_PRO_GLOBAL\FacebookAds\Object\CPASCollaborationRequest(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\CPASCollaborationRequest::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getCollaborativeAdsSuggestedPartners(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/collaborative_ads_suggested_partners', new \PYS_PRO_GLOBAL\FacebookAds\Object\CPASAdvertiserPartnershipRecommendation(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\CPASAdvertiserPartnershipRecommendation::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getCommerceMerchantSettings(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/commerce_merchant_settings', new \PYS_PRO_GLOBAL\FacebookAds\Object\CommerceMerchantSettings(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\CommerceMerchantSettings::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getContentDeliveryReport(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('end_date' => 'datetime', 'page_id' => 'unsigned int', 'platform' => 'platform_enum', 'position' => 'position_enum', 'start_date' => 'datetime', 'summary' => 'bool');
        $enums = array('platform_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\ContentDeliveryReportPlatformValues::getInstance()->getValues(), 'position_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\ContentDeliveryReportPositionValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/content_delivery_report', new \PYS_PRO_GLOBAL\FacebookAds\Object\ContentDeliveryReport(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\ContentDeliveryReport::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createCreateAndApplyPublisherBlockList(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('is_auto_blocking_on' => 'bool', 'name' => 'string', 'publisher_urls' => 'list<string>');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/create_and_apply_publisher_block_list', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createCustomConversion(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('advanced_rule' => 'string', 'custom_event_type' => 'custom_event_type_enum', 'default_conversion_value' => 'float', 'description' => 'string', 'event_source_id' => 'string', 'name' => 'string', 'rule' => 'string');
        $enums = array('custom_event_type_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\CustomConversionCustomEventTypeValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/customconversions', new \PYS_PRO_GLOBAL\FacebookAds\Object\CustomConversion(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\CustomConversion::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getEventSourceGroups(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/event_source_groups', new \PYS_PRO_GLOBAL\FacebookAds\Object\EventSourceGroup(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\EventSourceGroup::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createEventSourceGroup(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('event_sources' => 'list<string>', 'name' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/event_source_groups', new \PYS_PRO_GLOBAL\FacebookAds\Object\EventSourceGroup(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\EventSourceGroup::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getExtendedCredits(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('order_by_is_owned_credential' => 'bool');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/extendedcredits', new \PYS_PRO_GLOBAL\FacebookAds\Object\ExtendedCredit(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\ExtendedCredit::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createFranchiseProgram(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('business_asset_group' => 'string', 'creative_folder' => 'string', 'creative_spec_template_data' => 'map', 'description' => 'string', 'end_date' => 'datetime', 'name' => 'string', 'program_approval_type' => 'program_approval_type_enum', 'shared_custom_audience' => 'string', 'start_date' => 'datetime');
        $enums = array('program_approval_type_enum' => array('APPROVAL', 'PUBLIC'));
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/franchise_programs', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getInitiatedAudienceSharingRequests(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('recipient_id' => 'string', 'request_status' => 'request_status_enum');
        $enums = array('request_status_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessAssetSharingAgreementRequestStatusValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/initiated_audience_sharing_requests', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAssetSharingAgreement(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAssetSharingAgreement::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getInitiatedSharingAgreements(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('receiving_business_id' => 'string', 'request_status' => 'request_status_enum');
        $enums = array('request_status_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessAgreementRequestStatusValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/initiated_sharing_agreements', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAgreement(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAgreement::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function deleteInstagramAccounts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('instagram_account' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_DELETE, '/instagram_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getInstagramAccounts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/instagram_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\InstagramUser(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\InstagramUser::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getInstagramBusinessAccounts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/instagram_business_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\IGUser(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\IGUser::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function deleteManagedBusinesses(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('existing_client_business_id' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_DELETE, '/managed_businesses', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createManagedBusiness(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('child_business_external_id' => 'string', 'existing_client_business_id' => 'string', 'name' => 'string', 'sales_rep_email' => 'string', 'survey_business_type' => 'survey_business_type_enum', 'survey_num_assets' => 'unsigned int', 'survey_num_people' => 'unsigned int', 'timezone_id' => 'unsigned int', 'vertical' => 'vertical_enum');
        $enums = array('survey_business_type_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessSurveyBusinessTypeValues::getInstance()->getValues(), 'vertical_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessVerticalValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/managed_businesses', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createMoveAsset(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('asset_id' => 'string', 'client_id' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/move_asset', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getOfflineConversionDataSets(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/offline_conversion_data_sets', new \PYS_PRO_GLOBAL\FacebookAds\Object\OfflineConversionDataSet(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\OfflineConversionDataSet::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createOfflineConversionDataSet(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('auto_assign_to_new_accounts_only' => 'bool', 'description' => 'string', 'enable_auto_assign_to_accounts' => 'bool', 'is_mta_use' => 'bool', 'name' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/offline_conversion_data_sets', new \PYS_PRO_GLOBAL\FacebookAds\Object\OfflineConversionDataSet(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\OfflineConversionDataSet::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getOwnedAdAccounts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/owned_ad_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdAccount(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdAccount::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createOwnedAdAccount(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('adaccount_id' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/owned_ad_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getOwnedApps(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/owned_apps', new \PYS_PRO_GLOBAL\FacebookAds\Object\Application(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Application::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createOwnedApp(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('app_id' => 'Object');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/owned_apps', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function deleteOwnedBusinesses(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('client_id' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_DELETE, '/owned_businesses', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getOwnedBusinesses(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('child_business_external_id' => 'string', 'client_user_id' => 'int');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/owned_businesses', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createOwnedBusiness(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('child_business_external_id' => 'string', 'name' => 'string', 'page_permitted_tasks' => 'list<page_permitted_tasks_enum>', 'sales_rep_email' => 'string', 'shared_page_id' => 'string', 'survey_business_type' => 'survey_business_type_enum', 'survey_num_assets' => 'unsigned int', 'survey_num_people' => 'unsigned int', 'timezone_id' => 'unsigned int', 'vertical' => 'vertical_enum');
        $enums = array('page_permitted_tasks_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessPagePermittedTasksValues::getInstance()->getValues(), 'survey_business_type_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessSurveyBusinessTypeValues::getInstance()->getValues(), 'vertical_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessVerticalValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/owned_businesses', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getOwnedInstagramAccounts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/owned_instagram_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\InstagramUser(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\InstagramUser::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getOwnedPages(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/owned_pages', new \PYS_PRO_GLOBAL\FacebookAds\Object\Page(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Page::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createOwnedPage(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('code' => 'string', 'page_id' => 'int');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/owned_pages', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getOwnedPixels(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/owned_pixels', new \PYS_PRO_GLOBAL\FacebookAds\Object\AdsPixel(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\AdsPixel::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getOwnedProductCatalogs(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/owned_product_catalogs', new \PYS_PRO_GLOBAL\FacebookAds\Object\ProductCatalog(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\ProductCatalog::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createOwnedProductCatalog(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('catalog_segment_filter' => 'Object', 'catalog_segment_product_set_id' => 'string', 'da_display_settings' => 'Object', 'destination_catalog_settings' => 'map', 'flight_catalog_settings' => 'map', 'name' => 'string', 'parent_catalog_id' => 'string', 'store_catalog_settings' => 'map', 'vertical' => 'vertical_enum');
        $enums = array('vertical_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\ProductCatalogVerticalValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/owned_product_catalogs', new \PYS_PRO_GLOBAL\FacebookAds\Object\ProductCatalog(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\ProductCatalog::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getOwnedWhatsAppBusinessAccounts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/owned_whatsapp_business_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\WhatsAppBusinessAccount(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\WhatsAppBusinessAccount::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function deletePages(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('page_id' => 'int');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_DELETE, '/pages', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getPendingClientAdAccounts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/pending_client_ad_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAdAccountRequest(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAdAccountRequest::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getPendingClientApps(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/pending_client_apps', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessApplicationRequest(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessApplicationRequest::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getPendingClientPages(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/pending_client_pages', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessPageRequest(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessPageRequest::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getPendingOwnedAdAccounts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/pending_owned_ad_accounts', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAdAccountRequest(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAdAccountRequest::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getPendingOwnedPages(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/pending_owned_pages', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessPageRequest(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessPageRequest::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getPendingUsers(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('email' => 'string');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/pending_users', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessRoleRequest(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessRoleRequest::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getPicture(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('breaking_change' => 'breaking_change_enum', 'height' => 'int', 'redirect' => 'bool', 'type' => 'type_enum', 'width' => 'int');
        $enums = array('breaking_change_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\ProfilePictureSourceBreakingChangeValues::getInstance()->getValues(), 'type_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\ProfilePictureSourceTypeValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/picture', new \PYS_PRO_GLOBAL\FacebookAds\Object\ProfilePictureSource(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\ProfilePictureSource::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createPixelTo(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/pixel_tos', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getReceivedAudienceSharingRequests(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('initiator_id' => 'string', 'request_status' => 'request_status_enum');
        $enums = array('request_status_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessAssetSharingAgreementRequestStatusValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/received_audience_sharing_requests', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAssetSharingAgreement(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAssetSharingAgreement::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getReceivedSharingAgreements(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('request_status' => 'request_status_enum', 'requesting_business_id' => 'string');
        $enums = array('request_status_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessAgreementRequestStatusValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/received_sharing_agreements', new \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAgreement(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\BusinessAgreement::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getSystemUsers(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/system_users', new \PYS_PRO_GLOBAL\FacebookAds\Object\SystemUser(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\SystemUser::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createSystemUser(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('name' => 'string', 'role' => 'role_enum', 'system_user_id' => 'int');
        $enums = array('role_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\SystemUserRoleValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/system_users', new \PYS_PRO_GLOBAL\FacebookAds\Object\SystemUser(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\SystemUser::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getThirdPartyMeasurementReportDataset(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/third_party_measurement_report_dataset', new \PYS_PRO_GLOBAL\FacebookAds\Object\ThirdPartyMeasurementReportDataset(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\ThirdPartyMeasurementReportDataset::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createUploadEvent(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('aggregation_level' => 'aggregation_level_enum', 'conversion_end_date' => 'string', 'conversion_start_date' => 'string', 'event_status' => 'event_status_enum', 'lookback_window' => 'lookback_window_enum', 'match_universe' => 'match_universe_enum', 'timezone' => 'timezone_enum', 'upload_tag' => 'string');
        $enums = array('aggregation_level_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\MeasurementUploadEventAggregationLevelValues::getInstance()->getValues(), 'event_status_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\MeasurementUploadEventEventStatusValues::getInstance()->getValues(), 'lookback_window_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\MeasurementUploadEventLookbackWindowValues::getInstance()->getValues(), 'match_universe_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\MeasurementUploadEventMatchUniverseValues::getInstance()->getValues(), 'timezone_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\MeasurementUploadEventTimezoneValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/upload_event', new \PYS_PRO_GLOBAL\FacebookAds\Object\MeasurementUploadEvent(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\MeasurementUploadEvent::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getSelf(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'NODE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function updateSelf(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('name' => 'string', 'primary_page' => 'string', 'timezone_id' => 'unsigned int', 'two_factor_type' => 'two_factor_type_enum', 'vertical' => 'vertical_enum');
        $enums = array('two_factor_type_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessTwoFactorTypeValues::getInstance()->getValues(), 'vertical_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\BusinessVerticalValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/', new \PYS_PRO_GLOBAL\FacebookAds\Object\Business(), 'NODE', \PYS_PRO_GLOBAL\FacebookAds\Object\Business::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
}