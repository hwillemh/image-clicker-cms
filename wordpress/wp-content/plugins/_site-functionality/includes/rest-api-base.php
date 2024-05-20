<?php
/**
 * The REST API main class.
 *
 * @package    Meta Box
 * @subpackage MB Rest API
 */
/**
 * Meta Box Rest API class.
 */
class SMG_Rest_API_Base
{
    /**
     * List of media fields to filter.
     *
     * @var array
     * 
     * 
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'rest_api_init']);
    }
    protected const media_fields = array(
        'media',
        'file',
        'file_upload',
        'file_advanced',
        'image',
        'image_upload',
        'image_advanced',
        'plupload_image',
        'thickbox_image',
    );
    /**
     * List of fields that have no values.
     *
     * @var array
     */
    protected const no_value_fields = array(
        'heading',
        'custom_html',
        'divider',
        'button',
    );
    /**
     * Register new field 'meta_box' for all meta box's fields.
     */
    public function rest_api_init()
    {
        register_rest_field(
            'article',
            'article_meta',
            array(
                'get_callback' => array($this, 'get_post_meta'),
                'update_callback' => array($this, 'update_post_meta'),
            )
        );
    }
    /**
     * Get post meta for the rest API.
     *
     * @param array $object Post object.
     *
     * @return array
     */
    public static function get_post_meta($object)
    {
        $meta_boxes = rwmb_get_registry('meta_box')->get_by(array('object_type' => 'post'));
        $meta_boxes = array_filter($meta_boxes, function ($meta_box) use ($object) {
            return in_array($object['type'], $meta_box->post_types, true);
        });
        return SMG_Rest_API_Base::get_values($meta_boxes, $object['id']);
    }
    /**
     * Update post meta for the rest API.
     *
     * @param string|array $data   Post meta values in either JSON or array format.
     * @param object       $object Post object.
     */
    public function update_post_meta($data, $object)
    {
        $data = is_string($data) ? json_decode($data, true) : $data;
        foreach ($data as $field_id => $value) {
            $field = rwmb_get_registry('field')->get($field_id, $object->post_type);
            $this->update_value($field, $value, $object->ID);
        }
        do_action('rwmb_after_save_post', $object->ID);
    }
    /**
     * Update field value.
     *
     * @param array $field     Field data.
     * @param mixed $value     Field value.
     * @param int   $object_id Object ID.
     */
    protected function update_value($field, $value, $object_id)
    {
        $old = RWMB_Field::call($field, 'raw_meta', $object_id);
        $new = RWMB_Field::process_value($value, $object_id, $field);
        $new = RWMB_Field::filter('rest_value', $new, $field, $old, $object_id);
        // Call defined method to save meta value, if there's no methods, call common one.
        RWMB_Field::call($field, 'save', $new, $old, $object_id);
    }
    /**
     * Get supported types in Rest API.
     *
     * @param string $type 'post' or 'taxonomy'.
     *
     * @return array
     */
    protected function get_types($type = 'post')
    {
        $types = get_post_types(array(), 'objects');
        if ('taxonomy' === $type) {
            $types = get_taxonomies(array(), 'objects');
        }
        foreach ($types as $type => $object) {
            if (empty($object->show_in_rest)) {
                unset($types[$type]);
            }
        }
        return array_keys($types);
    }
    /**
     * Get all fields' values from list of meta boxes.
     *
     * @param array $meta_boxes Array of meta box object.
     *
     * @param int   $object_id  Object ID.
     * @param array $args       Additional params for helper function.
     *
     * @return array
     */
    protected static function get_values($meta_boxes, $object_id, $args = array())
    {
        $fields = array();
        foreach ($meta_boxes as $meta_box) {
            $fields = array_merge($fields, $meta_box->fields);
        }
        // Remove fields with no values.
        $fields = array_filter($fields, function ($field) {
            return !empty($field['id']) && !in_array($field['type'], SMG_Rest_API_Base::no_value_fields, true);
        });
        $values = array();
        foreach ($fields as $field) {
            $value = rwmb_get_value($field['id'], $args, $object_id);
            $value = SMG_Rest_API_Base::normalize_value($field, $value);
            if ($field['id'] == "article_item") {
                $value = SMG_Rest_API_Base::add_article_image_info($field, $value);
            }
            $values[$field['id']] = $value;
        }
        return $values;
    }
    function add_article_image_info($field, $value)
    {
        foreach ($value as $key => $item) {
            //d($item['item_caption']);
            if (isset($item['article_image'][0])) {
                $attachment_id = $item['article_image'][0];
                $src = wp_get_attachment_image_src($attachment_id);
                $srcset = wp_get_attachment_image_srcset($attachment_id, 'full');
                $value[$key]['article_image'] = [
                    'id' => $attachment_id,
                    'src' => $src[0],
                    'width' => $src[1],
                    'height' => $src[2],
                    'srcset' => $srcset,
                ];
            }
            if (isset($item['article_video']['video'][0])) {
                $attachment_id = $item['article_video']['video'][0];
                $src = wp_get_attachment_url($attachment_id);
                $value[$key]['article_video']['src'] = $src;
                //'width' => $src[1],
                //'height' => $src[2],
                // 'srcset' => $srcset,
            }
        }
        return $value;
    }
    /**
     * Normalize value.
     *
     * @param  array $field Field settings.
     * @param  mixed $value Field value.
     * @return mixed
     */
    static function normalize_value($field, $value)
    {
        $value = SMG_Rest_API_Base::normalize_group_value($field, $value);
        $value = SMG_Rest_API_Base::normalize_media_value($field, $value);
        return $value;
    }
    /**
     * Normalize group value.
     *
     * @param  array $field Field settings.
     * @param  mixed $value Field value.
     * @return mixed
     */
    static function normalize_group_value($field, $value)
    {
        if ('group' !== $field['type']) {
            return $value;
        }
        if (isset($value['_state'])) {
            unset($value['_state']);
        }
        foreach ($field['fields'] as $subfield) {
            if (empty($subfield['id']) || empty($value[$subfield['id']])) {
                continue;
            }
            $subvalue = $value[$subfield['id']];
            $subvalue = SMG_Rest_API_Base::normalize_value($subfield, $subvalue);
            $value[$subfield['id']] = $subvalue;
        }
        return $value;
    }
    /**
     * Normalize media value.
     *
     * @param  array $field Field settings.
     * @param  mixed $value Field value.
     * @return mixed
     */
    static function normalize_media_value($field, $value)
    {
        /*
         * Make sure values of file/image fields are always indexed 0, 1, 2, ...
         * @link https://github.com/wpmetabox/mb-rest-api/commit/31aa8fa445c188e8a71ebff80027acbcaa0fd268
         */
        if (is_array($value) && in_array($field['type'], SMG_Rest_API_Base::media_fields, true)) {
            $value = array_values($value);
        }
        return $value;
    }
}
