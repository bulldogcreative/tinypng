# TinyPNG ExpressionEngine Add-on

Visit [TinyPNG](https://tinypng.com/) for more information.

## Installation

1. [Sign up](https://tinypng.com/developers) for the Developer API and get your API key
2. [Download](https://bitbucket.org/bulldogcreative/tinypng/downloads) the zip file
3. Create the directory **tinypng** in /system/expressionengine/third_party/
4. Upload the files into the new directory
5. Login to ExpressionEngine
6. Click **Add-Ons** -> **Extensions**
7. Click **Install** on the TinyPNG row
8. Click **Settings** on the TinyPNG row
9. Enter your **API key** and click **Submit**
10. You're done

Any image you upload will be made smaller with TinyPNG. Your original file will be stored
in a folder called **_original**. You can still use your original file if you wish.

## Example usage

To use the TinyPNG version of the image.

    {exp:channel:entries channel="pages" limit="1"}
        <img alt="{title}" src="{image}" />
    {/exp:channel:entries}

To use the original image.

    {exp:channel:entries channel="pages" limit="1"}
        <img alt="{title}" src="{image:original}" />
    {/exp:channel:entries}
