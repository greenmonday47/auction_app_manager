<?php

if (!function_exists('validateImageUrl')) {
    /**
     * Validate if a URL is a valid image URL
     *
     * @param string $url
     * @return bool
     */
    function validateImageUrl($url)
    {
        if (empty($url)) {
            return true; // Empty URLs are allowed (optional field)
        }

        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check if URL has common image extensions
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $urlLower = strtolower($url);
        
        foreach ($imageExtensions as $ext) {
            if (strpos($urlLower, '.' . $ext) !== false) {
                return true;
            }
        }

        // If no extension found, still allow it (some CDNs don't use extensions)
        return true;
    }
}

if (!function_exists('getImageUrl')) {
    /**
     * Get image URL with fallback
     *
     * @param string|null $imageUrl
     * @param string $fallbackUrl
     * @return string
     */
    function getImageUrl($imageUrl, $fallbackUrl = '')
    {
        if (empty($imageUrl)) {
            return $fallbackUrl ?: base_url('assets/images/placeholder.jpg');
        }

        return $imageUrl;
    }
}

if (!function_exists('getImageThumbnail')) {
    /**
     * Get image thumbnail HTML
     *
     * @param string|null $imageUrl
     * @param string $alt
     * @param array $attributes
     * @return string
     */
    function getImageThumbnail($imageUrl, $alt = '', $attributes = [])
    {
        if (empty($imageUrl)) {
            return '';
        }

        $defaultAttributes = [
            'src' => $imageUrl,
            'alt' => $alt,
            'class' => 'img-thumbnail',
            'style' => 'max-width: 100%; height: auto;',
            'onerror' => 'this.style.display="none"'
        ];

        $attributes = array_merge($defaultAttributes, $attributes);
        
        $html = '<img';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . esc($value) . '"';
        }
        $html .= '>';

        return $html;
    }
} 