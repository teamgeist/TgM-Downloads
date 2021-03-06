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
* Faceted Search (alias ke_search  version > 2.4.1) indexer out of the box
* Frontend output supports German and English

## TODO
* Translate the flexform labels properly (Plugin settings)
* Add more option for default orderings
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
- 1.1.0 :
    * Feature - ke_search indexer
- 1.1.1 :
    * Add - some simple sorting settings in the plugin
- 1.2.0 :
    * Add - RTE Download description field
    * Add - You have now the possibility to show only the newest download (we compare the date inside the records, if there is no one, we take the crdate for comparison)
- 1.2.1 :
    * Bugfixes - Some minor template fixes and one exception when no download was available 
    * Add - German translation for the Backend Fields
- 1.2.2 :
    * Add - Sorting By Date fallback on crdate
    * Add - Some text cropping possibilities for the latest(newest) view
    * Add - The possibility to add the list page id in the plugin. So you can for example create a link inside your template which leads you show all downloads



