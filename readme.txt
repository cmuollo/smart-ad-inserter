=== Smart Ad Inserter ===
Contributors: cmuollo
Tags: ads, advertising, banner, ad inserter, server-side
Requires at least: 6.0
Tested up to: 6.x
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Inserimento server-side di banner pubblicitari con controllo granulare per posizione e Zero-CLS garantito.

== Description ==

Smart Ad Inserter e un plugin per WordPress progettato specificamente per ottimizzare l'esperienza utente ed i Core Web Vitals, con particolare attenzione all'azzeramento del Cumulative Layout Shift (CLS). A differenza dei comuni inseritori di annunci basati su script client-side (JS) che causano spiacevoli spostamenti del layout all'arrivo dei banner, Smart Ad Inserter effettua l'iniezione dei wrapper pubblicitari interamente lato server prima dell'invio dell'HTML al browser.

Il plugin pre-alloca lo spazio necessario per gli annunci impostando altezze minime personalizzate (differenziate per desktop e mobile). In questo modo, il browser web riserva preventivamente lo spazio corretto nel flusso del documento, garantendo stabiita visiva assoluta fin dal primo rendering grafico.

Il plugin include un pannello di gestione reattivo in 4 schede (Globale, Home, Articolo Singolo e Categorie/Archivi) per un controllo capillare delle posizioni pubblicitarie (Masthead, Sidebar, Grid Box ed ATF/BTF) tramite chiamate asincrone sicure collegate alla REST API di WordPress.

== Installation ==

1. Carica la cartella `smart-ad-inserter` all'interno della directory `/wp-content/plugins/` del tuo sito WordPress.
2. Attiva il plugin tramite la schermata "Plugin" nella bacheca di WordPress.
3. Accedi alla voce "Smart Ad Inserter" comparsa nella barra laterale sinistra dell'amministrazione di WordPress per configurare le posizioni dei tuoi banner.

== Frequently Asked Questions ==

= Il plugin è compatibile con Elementor? =
Si, Smart Ad Inserter e pienamente compatibile con Elementor, Gutenberg e con qualsiasi page builder moderno, in quanto sfrutta i filtri standard di WordPress come `the_content` ed i ganci widget nativi.

= I banner vengono iniettati via JavaScript? =
No. L'iniezione dell'HTML e dei placeholder per i banner avviene esclusivamente lato server (PHP) prima della compilazione della risposta HTML, garantendo che non vi sia alcun file JS del plugin caricato nel frontend pubblico.

== Changelog ==

= 1.0.0 =
* Prima release stabile.
* Iniezione server-side per 4 posizioni configurabili (Masthead, Sidebar, Grid Box, ATF/BTF).
* Zero-CLS: min-height pre-allocata lato server per desktop e mobile.
* Interfaccia amministrativa a schede con salvataggio asincrono tramite REST API protetta da Nonce.

== Upgrade Notice ==

= 1.0.0 =
Prima release stabile.
