# WP Core Updater Plugin

## Overview
This plugin, **WP Core Updater**, is designed to manage WordPress core updates.

## Important Notes
- This plugin is designed to work with **traditional WordPress installations**.
- If you're using a **WordPress setup with [Bedrock](https://roots.io/bedrock/)**, this plugin will **not function as expected** due to the different file structure in Bedrock.

## Requirements
- **Composer** is required to install dependencies for this plugin.
- The plugin makes a GET request to retrieve WordPress core update information from the following endpoint:
  - `https://websiteredev.com/wp-downloads/version_data.json`



## Composer Setup

### Prerequisites

Ensure you have Composer installed on your machine. If not, [install Composer](https://getcomposer.org/doc/00-intro.md).

### Installation

1. Clone this repository to your WordPress plugins directory:
```bash
   git clone git@github.com:Website-Redev/wp-core-updater.git
```

2. Navigate to the plugin directory:
```bash
  cd wp-core-updater
```

3. Install the required dependencies using Composer:
```bash
  composer install
```

4. If needed, update Composer dependencies:
```bash
  composer update
```

## Integrating the Plugin with a Custom Theme

For the `wp-core-updater` plugin to work properly while using a custom theme, follow these steps:

### Step 1: Create Folder Structure
Inside your custom theme's folder (e.g., starter-theme), create a directory for the plugin files. You can name it **lib** or any other folder name you prefer.

```bash
starter-theme/
├── lib/
│   └── wp-core-updater/  # Place the plugin files here
└── functions.php
```

### Step 2: Include Plugin in functions.php
Open the `functions.php` file in your theme and include the wp-core-updater plugin by adding the following line of code:

```php
require_once get_template_directory() . '/lib/wp-core-updater/wp-core-updater.php';
```

This ensures that the wp-core-updater plugin is loaded when the theme is active.

