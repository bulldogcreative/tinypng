# TinyPNG ExpressionEngine Add-on

Visit [TinyPNG](https://tinypng.com/) for more information.

## Installation

1. [Sign up](https://tinypng.com/developers) for the Developer API and get your API key
2. [Download](https://github.com/BulldogCreative/tinypng/releases) the zip file
3. Create the directory **tinypng** in /system/expressionengine/third_party/
4. Upload the files into the new directory
5. Login to ExpressionEngine
6. Click **Developers** -> **Add-Ons**
7. Click **Install** on the TinyPNG row
8. Click **Settings** on the TinyPNG row
9. Enter your **TingPNG API Key** and click **Save Settings**
10. You're done

Any image you upload will be made smaller with TinyPNG. Your original file will be stored
in a folder called **_original**. You can still use your original file if you wish.

## Example usage

To use the TinyPNG version of the image.

    {exp:channel:entries channel="pages" limit="1"}
        <img alt="{title}" src="{image}" />
    {/exp:channel:entries}

![TinyPNG version](samples/after.png)

To use the original image.

    {exp:channel:entries channel="pages" limit="1"}
        <img alt="{title}" src="{image:original}" />
    {/exp:channel:entries}

![Original version](samples/before.png)

## Change Log

## Version 2.0.0

December 4th, 2017

Added support for ExpressionEngine 4.0.

### Version 1.0.3

April 15th, 2015

Bug Fixes:

* EE was showing an error when uploading a file that already existed.

### Version 1.0.2

March 27th, 2015

Bug Fixes:

* EE was showing the filesize of the original image.

General Changes:

* Site ID was set to one. It now uses $data["site_id"] to get the site id.

### Version 1.0.0

March 24, 2015

Initial release

## photo credit

Photo by [Levi Bare](https://unsplash.com/photos/xCfHL21VpDk?utm_source=unsplash&utm_medium=referral&utm_content=creditCopyText)
on
[Unsplash](https://unsplash.com/?utm_source=unsplash&utm_medium=referral&utm_content=creditCopyText).
