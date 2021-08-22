# WP REST API SVG Creator

Adds a custom endpoint that allows authenticated users to add SVG's to WordPress' Media Library by posting SVG markup.

## Endpoint

**POST** `/wp-json/svg/v1/media`

### Usage

| Args   | Description                                               |
| ------ | --------------------------------------------------------- |
| markup | The SVG markup (required)                                 |
| title  | Used to build the SVG filename                            |
| alt    | The alt text for the image in the WordPress Media Library |
