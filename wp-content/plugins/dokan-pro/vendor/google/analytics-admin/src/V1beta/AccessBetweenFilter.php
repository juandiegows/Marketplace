<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/admin/v1beta/access_report.proto

namespace Google\Analytics\Admin\V1beta;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * To express that the result needs to be between two numbers (inclusive).
 *
 * Generated from protobuf message <code>google.analytics.admin.v1beta.AccessBetweenFilter</code>
 */
class AccessBetweenFilter extends \Google\Protobuf\Internal\Message
{
    /**
     * Begins with this number.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1beta.NumericValue from_value = 1;</code>
     */
    private $from_value = null;
    /**
     * Ends with this number.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1beta.NumericValue to_value = 2;</code>
     */
    private $to_value = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Analytics\Admin\V1beta\NumericValue $from_value
     *           Begins with this number.
     *     @type \Google\Analytics\Admin\V1beta\NumericValue $to_value
     *           Ends with this number.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Analytics\Admin\V1Beta\AccessReport::initOnce();
        parent::__construct($data);
    }

    /**
     * Begins with this number.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1beta.NumericValue from_value = 1;</code>
     * @return \Google\Analytics\Admin\V1beta\NumericValue|null
     */
    public function getFromValue()
    {
        return $this->from_value;
    }

    public function hasFromValue()
    {
        return isset($this->from_value);
    }

    public function clearFromValue()
    {
        unset($this->from_value);
    }

    /**
     * Begins with this number.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1beta.NumericValue from_value = 1;</code>
     * @param \Google\Analytics\Admin\V1beta\NumericValue $var
     * @return $this
     */
    public function setFromValue($var)
    {
        GPBUtil::checkMessage($var, \Google\Analytics\Admin\V1beta\NumericValue::class);
        $this->from_value = $var;

        return $this;
    }

    /**
     * Ends with this number.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1beta.NumericValue to_value = 2;</code>
     * @return \Google\Analytics\Admin\V1beta\NumericValue|null
     */
    public function getToValue()
    {
        return $this->to_value;
    }

    public function hasToValue()
    {
        return isset($this->to_value);
    }

    public function clearToValue()
    {
        unset($this->to_value);
    }

    /**
     * Ends with this number.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1beta.NumericValue to_value = 2;</code>
     * @param \Google\Analytics\Admin\V1beta\NumericValue $var
     * @return $this
     */
    public function setToValue($var)
    {
        GPBUtil::checkMessage($var, \Google\Analytics\Admin\V1beta\NumericValue::class);
        $this->to_value = $var;

        return $this;
    }

}
