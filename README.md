# TgM Downloads
Simple Extension that gives you the possibility to show downloads filtered by TYPO3 categories. Supports DataTables and Pagination

## Description 
You can create new download entrys (one entry, one download). 
One entry has:
* Title
* Date
* The download file (FAL)
* TYPO3 categories

After the creation of new downloads, you need to setup the TgM Download plugin, on the page where the downloads should be shown.
And Voilà, you will see the downloads on your website.

Inside the plugin you can choose between the normal TYPO3 pagination or DataTables (https://datatables.net/).
Here you can also set the categories that you wanna display.

## Features 
* Pagination
* DataTables
* Download Counter
* Filter: You can use TYPO3 categories for your downloads and so control your output, thanks filter settings inside the plugin.
* Frontend output supports German and English

## TODO
* Translate the flexform labels(Plugin settings)
* Add option for default orderings
* Add a possibility to limit the plugin output on a single storage id (folder/page)

### Nice to know
* The plugin will gather all entrys for the entierly TYPO3 system, no storage puid needed. 

### Changelog
**BETA**
- 1.0.0 : First public push on github
- 1.0.1 :
    * Add - Multi usage on the same page
    * Add - Changelog in Readme file
    * Bugfix - No rendering without pagination 


