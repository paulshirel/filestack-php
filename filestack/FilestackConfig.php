<?php
namespace Filestack;

/**
 * Filestack config constants, such as base URLs
 */
class FilestackConfig
{
    const API_URL = 'https://www.filestackapi.com/api';
    const CDN_URL = 'https://cdn.filestackcontent.com';

    const ALLOWED_ATTRS = [
        'ascii' => [
            'b', 'c', 'f', 'r', 's',
            'background', 'colored', 'foreground', 'reverse', 'size'
        ],
        'blackwhite' => [
            't', 'threshold'
        ],
        'blur' => [
            'a', 'amount'
        ],
        'border' => [
            'b', 'c', 'w',
            'background', 'color', 'width'
        ],
        'collage' => [
            'a', 'c', 'f', 'i', 'm', 'w', 'h',
            'autorotate', 'color', 'files', 'fit', 'margin', 'width', 'height'
        ],
        'circle' => [
            'b', 'background'
        ],
        'compress' => [
            'm', 'metadata'
        ],
        'crop' => [
            'd', 'dim'
        ],
        'debug' => [],
        'detect_faces' => [
            'c', 'e', 'n', 'x',
            'color', 'export', 'minSize', 'maxSize'
        ],
        'enhance' => [
        ],
        'modulate' => [
            'b', 'h', 's',
            'brightness', 'hue', 'saturation'
        ],
        'monochrome' => [
        ],
        'negative' => [
        ],
        'oil_paint' => [
            'a', 'amount'
        ],
        'output' => [
            'a', 'b', 'c', 'd', 'f', 'i', 'o', 'p', 'q', 'r', 's', 't',
            'background', 'colorspace', 'compress', 'density', 'docinfo', 'filetype',
            'page', 'pageformat', 'pageorientation', 'quality', 'secure', 'strip'
        ],
        'partial_blur' => [
            'a', 'l', 'o', 't',
            'amount', 'blur', 'objects', 'type'
        ],
        'partial_pixelate' => [
            'a', 'l', 'o', 't',
            'amount', 'blur', 'objects', 'type'
        ],
        'pixelate' => [
            'a','amount'
        ],
        'polaroid' => [
            'b', 'c', 'r',
            'background', 'color', 'rotate'
        ],
        'quality' => [
            'v', 'value'
        ],
        'redeye' => [
        ],
        'resize' => [
            'w', 'h', 'f', 'a',
            'width', 'height', 'fit', 'align'
        ],
        'rounded_corners' => [
            'b', 'l', 'r',
            'background', 'blur', 'radius'
        ],
        'rotate' => [
            'b', 'd', 'e',
            'background', 'deg', 'exif'
        ],
        'sepia' => [
            't', 'tone'
        ],
        'sharpen' => [
            'a', 'amount'
        ],
        'shadow' => [
            'b', 'l', 'o', 'v',
            'background', 'blur', 'opacity', 'vector'
        ],
        'store' => [
            'a', 'b', 'c', 'f', 'l', 'p', 'r',
            'access', 'base64decode', 'container', 'filename', 'location', 'path', 'region'
        ],
        'torn_edges' => [
            'b', 's',
            'background', 'spread'
        ],
        'upscale' => [
            'n', 's', 'u',
            'noise', 'style', 'upscale'
        ],
        'urlscreenshot' => [
            'a', 'd', 'm', 'w', 'h',
            'agent', 'delay', 'mode', 'width', 'height'
        ],
        'video_convert' => [
            'a', 'b', 'c', 'e', 'f', 'l', 'o', 'p', 'r', 'w', 'h', 's', 't', 'u',
            'access', 'aspect_mode',
            'audio_bitrate', 'audio_channels', 'audio_sample_rate',
            'clip_length', 'clip_offset', 'container', 'extname', 'fps', 'force',
            'keyframe_interval', 'location', 'path', 'preset', 'title', 'two_pass',
            'width', 'height', 'upscale', 'video_bitrate',
            'watermark_bottom', 'watermark_left', 'watermark_right', 'watermark_top',
            'watermark_width', 'watermark_height', 'watermark_url'
        ],
        'vignette' => [
            'a', 'b', 'm',
            'amount', 'background', 'blurmode'
        ],
        'watermark' => [
            'f', 'p', 's',
            'file', 'position', 'size'
        ],
        'zip' => []
    ];

    /**
     * Create the URL to send requests to based on action type
     *
     * @param string                $action     action type, possible values are:
     *                                          store, download, get_content, delete
     * @param string                $api_key    Filestack API Key
     * @param array                 $options    array of options
     * @param FilestackSecurity     $security   Filestack Security object
     *
     * @return string (url)
     */
    public static function createUrl($action, $api_key, $options=[], $security=null)
    {
        // lower case all keys
        $options = array_change_key_case($options, CASE_LOWER);
        $url = '';
        $append_security = false;

        switch ($action) {
            case 'debug':
                $url = sprintf('%s/%s/debug/%s',
                    self::CDN_URL,
                    $api_key,
                    $options['transform_str']
                );

                if ($security) {
                    $append_security = true;
                }
                break;

            case 'delete':
            case 'overwrite':
                $url = sprintf('%s/file/%s?key=%s',
                    self::API_URL,
                    $options['handle'],
                    $api_key
                );

                if ($security) {
                    $append_security = true;
                }
                break;

            /* transformation calls */
            case 'transform':
                $base_url = sprintf('%s/%s',
                    self::CDN_URL,
                    $api_key);

                // security in a different format for transformations
                $security_str = $security ? sprintf('/security=policy:%s,signature:%s',
                        $security->policy,
                        $security->signature) : '';

                // build url for transform or zip
                $url = sprintf($base_url . $security_str . '/%s/%s',
                    $options['tasks_str'],
                    $options['handle']
                );
                break;

            case 'upload':
                $allowed_options = [
                    'filename', 'mimetype', 'path', 'container', 'access', 'base64decode'
                ];

                // set location to S3 if not passed in
                $location = 'S3';
                if (array_key_exists('location', $options)) {
                    $location = $options['location'];
                }

                $url = sprintf('%s/store/%s?key=%s',
                    self::API_URL,
                    $location,
                    $api_key);

                foreach ($options as $key => $value) {
                    if (in_array($key, $allowed_options)) {
                        $url .= "&$key=$value";
                    }
                }

                if ($security) {
                    $append_security = true;
                }
                break;

            default:
                break;
        }

        /**
         * sign url if security is passed in and flag set
         */
        if ($security && $append_security) {
            $url = $security->signUrl($url);
        }

        return $url;
    }

    /**
     * Get current version of this library (from VERSION file)
     */
    public static function getVersion()
    {
        $version = file_get_contents(__DIR__ . '/../VERSION');
        return trim($version);
    }
}
