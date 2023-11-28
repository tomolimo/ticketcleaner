# Ticket Cleaner
Home for Ticket Cleaner GLPI plugin

See wiki for filter explanations: https://github.com/tomolimo/ticketcleaner/wiki

## 4.0.2
GLPI 10.0 compatible release

## 3.0.0
GLPI 9.5 compatible release

## 2.5.3
Fixed issue with filter's name and comment

## 2.5.2
Typo fix

## 2.5.0
Compatibility with GLPI 9.4

## 2.4.0
Compatibility with GLPI 9.3

## 2.4.2
Fixed issue with filter's name and comment

## 2.4.1
Typo fix

## 2.3.5
Fixed issue with filter's name and comment

## 2.3.4
Typo fix

## 2.3.3
Compatibility with GLPI 9.2
Fixed issue with the \r\n

## 2.3.1
Remove functions for 9.1 compatibility

## 2.1.0
Beware that, when upgrading from a version lower than 2.0.0, this new release will not keep your existing filters. You'll have to input them again with the new interface that permits to edit them directly into GLPi.
Your former filters will be copied into a backup table that you may edit via your preferred mySQL query editor (table name is `backup_glpi_plugin_ticketcleaner_filters`).
You'll have to combine your former filters to get the new ones that will be entered into the new table, or to create new one from scratch.

This version also brings the possibility to debug your regex using the GLPi debug mode (see wiki).

Added arTableExists and arFieldExists to be compatible with 9.2

## 2.0.5
Added a filter to delete file tags from ticket content when files are deleted from list

## 2.0.4
Changed the internal mechanism to check and delete attached pictures when in the 'pictures' folder. Fixes #4

## 2.0.3
Added 'UTF-8' as default charset for htmlentities and html_entity_decode, fixes #3

## 2.0.2
Added a test to prevent menu adding when not activated.
Added possibility to do online translation

## 2.0.1
Extends 'regex' and 'replacement' size (instead of VARCHAR will use TEXT field type).

## Older releases
Older releases are here: https://forge.glpi-project.org/projects/ticketcleaner
