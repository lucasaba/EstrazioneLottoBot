EstrazioneLottoBot
==================

E un progetto Symfony per provare a interagire con telegram.

Come funziona
=============

C'è solo un controller (Default) che viene sfruttato per fare i test.

Le rotte /ultima-estrazione e /estrazione/{numero}/{anno} si occupano
di recuperare le informazioni dal sito di AAMS 
(https://www.agenziadoganemonopoli.gov.it/portale/monopoli/giochi)
e le mostrano in locale.

La rotta /webhook/update/{secret} è il webhook da registrare su telegram
per ricevere gli aggiornamenti

La maggior parte del lavoro viene fatto dal servizio ScaricaEstrazione

Per provare il bot, usando Telegram, cerca @EstrazioneLottoBot
