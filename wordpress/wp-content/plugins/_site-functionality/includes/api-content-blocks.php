<?php

class SMG_Api_Content_Blocks
{

    function __construct()
    {
        add_action('rest_api_init', [$this, 'rest_api_init']);
    }

    function rest_api_init()
    {
        register_rest_field(
            ['page', 'event', 'neighborhood_partner', 'historic_event'],
            'content_blocks',
            array(
                'get_callback' => array($this, 'get_page_content_blocks'),
            )
        );
        register_rest_field(
            ['event'],
            'event_title',
            array(
                'get_callback' => array($this, 'get_event_title'),
            )
        );
    }

    public function get_event_title($object)
    {

        return get_the_title($object["id"]);
    }
    public function get_page_content_blocks($object)
    {

        $content = parse_blocks(get_the_content($object["id"]));
        $blocks = $this->parse_content_blocks($content);
        return $blocks;
    }
    function parse_content_blocks($blocks)
    {
        $parsed_blocks = [];
        foreach ($blocks as $key => $block) {

            if ($block['blockName'] != null) {
                switch ($block['blockName']) {
                    case 'core/video':
                        $src = wp_get_attachment_url($block['attrs']['id']);

                        $block["attrs"]["videoUrl"] = $src;
                    case "core/image":
                        //$image = SMG_Api_Helpers::get_image($block['attrs']["id"]);
                        $src =  wp_get_attachment_image_src($block['attrs']['id'], 'large');
                        $block["attrs"]["imageUrl"] = isset($src[0]) ? $src[0] : null;
                        $metadata = wp_get_attachment_metadata($block['attrs']['id']);
                        $block["attrs"]["caption"] = isset($metadata['image_meta']) ? $metadata['image_meta']['caption'] : null;
                        break;
                    case "tth/event-form":
                }
                $block["innerBlocks"] = $this->parse_content_blocks($block["innerBlocks"]);
                $parsed_blocks[] = $block;
            }
        }
        return $parsed_blocks;
    }
    function get_image_attrs($imageId)
    {
        $src =  wp_get_attachment_image_src($imageId);
        $array = [
            'srcset' =>  wp_get_attachment_image_srcset($imageId),
            'sizes' => wp_get_attachment_image_sizes($imageId),
            'thumbnail' => wp_get_attachment_image_url($imageId),
            //'imageID' =>  wp_get_attachment_image_src($imageId),
            'width' => $src[1],
            'height' => $src[2],
        ];
        return $array;
    }
}
