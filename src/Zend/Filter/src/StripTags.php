<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

use Traversable;
use Zend\Stdlib\ArrayUtils;

class StripTags extends AbstractFilter
{
    /**
     * Unique ID prefix used for allowing comments
     */
    const UNIQUE_ID_PREFIX = '__Zend_Filter_StripTags__';

    /**
     * Array of allowed tags and allowed attributes for each allowed tag
     *
     * Tags are stored in the array keys, and the array values are themselves
     * arrays of the attributes allowed for the corresponding tag.
     *
     * @var array
     */
    protected $tagsAllowed = [];

    /**
     * Array of allowed attributes for all allowed tags
     *
     * Attributes stored here are allowed for all of the allowed tags.
     *
     * @var array
     */
    protected $attributesAllowed = [];

    /**
     * Sets the filter options
     * Allowed options are
     *     'allowTags'     => Tags which are allowed
     *     'allowAttribs'  => Attributes which are allowed
     *     'allowComments' => Are comments allowed ?
     *
     * @param  string|array|Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if ((! is_array($options)) || (is_array($options) && ! array_key_exists('allowTags', $options) &&
            ! array_key_exists('allowAttribs', $options) && ! array_key_exists('allowComments', $options))) {
            $options = func_get_args();
            $temp['allowTags'] = array_shift($options);
            if (! empty($options)) {
                $temp['allowAttribs'] = array_shift($options);
            }

            if (! empty($options)) {
                $temp['allowComments'] = array_shift($options);
            }

            $options = $temp;
        }

        if (array_key_exists('allowTags', $options)) {
            $this->setTagsAllowed($options['allowTags']);
        }

        if (array_key_exists('allowAttribs', $options)) {
            $this->setAttributesAllowed($options['allowAttribs']);
        }
    }

    /**
     * Returns the tagsAllowed option
     *
     * @return array
     */
    public function getTagsAllowed()
    {
        return $this->tagsAllowed;
    }

    /**
     * Sets the tagsAllowed option
     *
     * @param  array|string $tagsAllowed
     * @return self Provides a fluent interface
     */
    public function setTagsAllowed($tagsAllowed)
    {
        if (! is_array($tagsAllowed)) {
            $tagsAllowed = [$tagsAllowed];
        }

        foreach ($tagsAllowed as $index => $element) {
            // If the tag was provided without attributes
            if (is_int($index) && is_string($element)) {
                // Canonicalize the tag name
                $tagName = strtolower($element);
                // Store the tag as allowed with no attributes
                $this->tagsAllowed[$tagName] = [];
            } elseif (is_string($index) && (is_array($element) || is_string($element))) {
                // Otherwise, if a tag was provided with attributes
                // Canonicalize the tag name
                $tagName = strtolower($index);
                // Canonicalize the attributes
                if (is_string($element)) {
                    $element = [$element];
                }
                // Store the tag as allowed with the provided attributes
                $this->tagsAllowed[$tagName] = [];
                foreach ($element as $attribute) {
                    if (is_string($attribute)) {
                        // Canonicalize the attribute name
                        $attributeName = strtolower($attribute);
                        $this->tagsAllowed[$tagName][$attributeName] = null;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Returns the attributesAllowed option
     *
     * @return array
     */
    public function getAttributesAllowed()
    {
        return $this->attributesAllowed;
    }

    /**
     * Sets the attributesAllowed option
     *
     * @param  array|string $attributesAllowed
     * @return self Provides a fluent interface
     */
    public function setAttributesAllowed($attributesAllowed)
    {
        if (! is_array($attributesAllowed)) {
            $attributesAllowed = [$attributesAllowed];
        }

        // Store each attribute as allowed
        foreach ($attributesAllowed as $attribute) {
            if (is_string($attribute)) {
                // Canonicalize the attribute name
                $attributeName = strtolower($attribute);
                $this->attributesAllowed[$attributeName] = null;
            }
        }

        return $this;
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @todo   improve docblock descriptions
     * @param  string $value
     * @return string|mixed
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }
        $value = (string) $value;

        // Strip HTML comments first
        $open     = '<!--';
        $openLen  = strlen($open);
        $close    = '-->';
        $closeLen = strlen($close);
        while (($start = strpos($value, $open)) !== false) {
            $end = strpos($value, $close, $start + $openLen);

            if ($end === false) {
                $value = substr($value, 0, $start);
            } else {
                $value = substr($value, 0, $start) . substr($value, $end + $closeLen);
            }
        }

        // Initialize accumulator for filtered data
        $dataFiltered = '';
        // Parse the input data iteratively as regular pre-tag text followed by a
        // tag; either may be empty strings
        preg_match_all('/([^<]*)(<?[^>]*>?)/', (string) $value, $matches);

        // Iterate over each set of matches
        foreach ($matches[1] as $index => $preTag) {
            // If the pre-tag text is non-empty, strip any ">" characters from it
            if (strlen($preTag)) {
                $preTag = str_replace('>', '', $preTag);
            }
            // If a tag exists in this match, then filter the tag
            $tag = $matches[2][$index];
            if (strlen($tag)) {
                $tagFiltered = $this->_filterTag($tag);
            } else {
                $tagFiltered = '';
            }
            // Add the filtered pre-tag text and filtered tag to the data buffer
            $dataFiltered .= $preTag . $tagFiltered;
        }

        // Return the filtered data
        return $dataFiltered;
    }

    /**
     * Filters a single tag against the current option settings
     *
     * @param  string $tag
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _filterTag($tag)
    {
        // @codingStandardsIgnoreEnd
        // Parse the tag into:
        // 1. a starting delimiter (mandatory)
        // 2. a tag name (if available)
        // 3. a string of attributes (if available)
        // 4. an ending delimiter (if available)
        $isMatch = preg_match('~(</?)(\w*)((/(?!>)|[^/>])*)(/?>)~', $tag, $matches);

        // If the tag does not match, then strip the tag entirely
        if (! $isMatch) {
            return '';
        }

        // Save the matches to more meaningfully named variables
        $tagStart      = $matches[1];
        $tagName       = strtolower($matches[2]);
        $tagAttributes = $matches[3];
        $tagEnd        = $matches[5];

        // If the tag is not an allowed tag, then remove the tag entirely
        if (! isset($this->tagsAllowed[$tagName])) {
            return '';
        }

        // Trim the attribute string of whitespace at the ends
        $tagAttributes = trim($tagAttributes);

        // If there are non-whitespace characters in the attribute string
        if (strlen($tagAttributes)) {
            // Parse iteratively for well-formed attributes
            preg_match_all('/([\w-]+)\s*=\s*(?:(")(.*?)"|(\')(.*?)\')/s', $tagAttributes, $matches);

            // Initialize valid attribute accumulator
            $tagAttributes = '';

            // Iterate over each matched attribute
            foreach ($matches[1] as $index => $attributeName) {
                $attributeName      = strtolower($attributeName);
                $attributeDelimiter = empty($matches[2][$index]) ? $matches[4][$index] : $matches[2][$index];
                $attributeValue     = $matches[3][$index] === '' ? $matches[5][$index] : $matches[3][$index];

                // If the attribute is not allowed, then remove it entirely
                if (! array_key_exists($attributeName, $this->tagsAllowed[$tagName])
                    && ! array_key_exists($attributeName, $this->attributesAllowed)) {
                    continue;
                }
                // Add the attribute to the accumulator
                $tagAttributes .= " $attributeName=" . $attributeDelimiter
                                . $attributeValue . $attributeDelimiter;
            }
        }

        // Reconstruct tags ending with "/>" as backwards-compatible XHTML tag
        if (str_contains($tagEnd, '/')) {
            $tagEnd = " $tagEnd";
        }

        // Return the filtered tag
        return $tagStart . $tagName . $tagAttributes . $tagEnd;
    }
}
