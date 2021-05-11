# ChmiWarnings

- Znáte [stránku s aktuálními výstrahami](https://www.chmi.cz/files/portal/docs/meteo/om/vystrahy/index.html) Českého hydrometeorologického ústavu?
- A máte meteostanici, kde byste chtěli zobrazovat výstrahy ČHMÚ pro vaši lokalitu?
- A poháníte tu meteostanici nějakým malým mikrokontrolérem, pro který je trochu problém parsovat **megabajtový XML soubor** (a vlastně i jen ho stáhnout přes https ze serveru)?

**Tato aplikace řeší váš problém!**

Aplikaci **ChmiWarnings** spustíte na nějakém serveru s PHP a ona pro své klienty odvede všechnu špinavou práci. Konzumentovi nabízí jednoduchou REST službu - řeknu ID okresu, který mne zajímá, a dostanu snadno zpracovatelný JSON s výstrahami pro můj okres. Drahé stahování a parsování XML se provede na serveru, kde je výkon levný.

Aplikace **kešuje** soubor s výstrahami, z webu ČHMÚ ho tedy stahuje maximálně jednou za hodinu bez ohledu na to, jak často se klienti ptají. Kešuje také odpovědi - pokud se bude konzument ptát stále dokola, parsování XML se bude dělat jen jednou za deset minut (nebo když se stáhne nový soubor).


---
# Ukázka použití a popis parametrů

ČHMÚ mapuje výstrahu na okresy ("obce s rozšířenou působností"). Takže nejprve si v [číselníku CISORP](http://apl.czso.cz/iSMS/cisdet.jsp?kodcis=65) najděte svůj okres. Třeba pro Jablonec nad Nisou je kód 5103.

No a pak zavolejte aplikaci. Zkuste si to na demo serveru: 
https://lovecka.info/ChmiWarnings1/chmi/vystrahy/5103

Odpověď může vypadat takto:

```json
{
   "current_time":1620755607,
   "events":[
      {
         "text":"Silné bouřky",
         "detailed_text":"Očekává se ojedinělý výskyt silných bouřek s úhrny ojediněle nad 40 mm a kroupami.",
         "instruction":"Lokálně se očekává přívalový déšť s ojedinělým rozvodněním malých toků, zatopení podchodů, podjezdů, sklepů apod. Nárazy větru mohou lámat větve stromů. Nebezpečí mohou představovat také kroupy a blesky. Je třeba dbát na bezpečnost zejména s ohledem na nebezpečí zásahu bleskem a úrazu padajícími a poletujícími předměty. Při řízení vozidla v bouřce snížit rychlost jízdy a jet velmi opatrně. Vývoj a postup bouřek lze sledovat na výstupech z meteorologických radarů na internetu ČHMÚ www.chmi.cz nebo v aplikaci mobilního telefonu.",
         "time_start_i":"2021-05-12T13:00:00+02:00",
         "time_end_i":"2021-05-12T22:00:00+02:00",
         "category":{
            "response":"Shelter",
            "urgency":"Future",
            "severity":"Moderate",
            "certainty":"Likely"
         },
         "color":"yellow",
         "type":"Thunderstorm",
         "time_start_e":1620817200,
         "time_start_t":"zítra 13:00",
         "time_end_e":1620849600,
         "time_end_t":"zítra 22:00",
         "in_progress":"N"
      }
   ]
}
```

Nojo, ale váš mikrokontrolér je ještě menší a i tohle je pro něj moc? Přidejte parametr **kratke** - bude-li mít hodnotu 1, zmizí sekce "category" a text "instructions". Bude-li mít 2, zmizí i "detailed text".

Zavolám
https://lovecka.info/ChmiWarnings1/chmi/vystrahy/5103?kratke=2 a dostanu:

```json
{
   "current_time":1620755735,
   "events":[
      {
        "text": "Silný vítr",
        "time_start_i": "2021-05-07T12:00:00+02:00",
        "time_end_i": "2021-05-07T21:00:00+02:00",
        "color": "yellow",
        "type": "Wind",
        "time_start_e": 1620381600,
        "time_start_t": "pá 7.5. 12:00",
        "time_end_e": 1620414000,
        "time_end_t": "pá 7.5. 21:00",
        "in_progress": "N"
      }
   ]
}
```

OK, ale co když můj displej **neumí českou diakritiku**? I na to pamatujeme. Přidejte parametr **odhackuj=1**.

Zavolám
https://lovecka.info/ChmiWarnings1/chmi/vystrahy/5103?kratke=1&odhackuj=1
a dostanu text bez háčků a čárek:

```json
{
   "current_time":1620755928,
   "events":[
      {
         "text":"Silne bourky",
         "detailed_text":"Ocekava se ojedinely vyskyt silnych bourek s uhrny ojedinele nad 40 mm a kroupami.",
         "time_start_i":"2021-05-12T13:00:00+02:00",
         "time_end_i":"2021-05-12T22:00:00+02:00",
         "color":"yellow",
         "type":"Thunderstorm",
         "time_start_e":1620817200,
         "time_start_t":"zitra 13:00",
         "time_end_e":1620849600,
         "time_end_t":"zitra 22:00",
         "in_progress":"N"
      }
   ]
}
```

---
# Popis vrácených hodnot

V obálce je v položce **current_time**  vrácen aktuální čas (Unix epoch time). A dále následuje pole výstrah **events**. Výstrah může být obecné množství - nemusí být žádná, může jich být více.

Položky výstrahy:
* **time_start_X** - Čas, od kdy výstraha platí. Je ve třech variantách:
    * time_start_i - ISO formát.
    * time_start_e - Unix epoch time.
    * time_start_t - Lidsky čitelný čas.
* **time_end_X** - Čas, do kdy výstraha platí, taktéž třikrát. Nemusí být vyplněno!
* **in_progress** - Je právě čas, kdy výstraha platí? Y/N
* **color** - Barevné kódování výstrahy (např. pro ikonu):
    * green - výhledová událost, nedůležitá
    * yellow - výstraha
    * orange - velká výstraha
    * red - z nebe padají žraloci
* **type** - Typ výstrahy dle [návodu ČHMÚ](https://www.chmi.cz/files/portal/docs/meteo/om/vystrahy/doc/Dokumentace_CAP.pdf):
    * Wind
    * snow-ice
    * Thunderstorm
    * Fog
    * high-temperature
    * low-temperature
    * coastalevent
    * forest-fire
    * avalanches
    * Rain
    * unknown
    * flooding
    * rain-flood
* **text** - Nadpis události.
* **detailed_text** - Detailní popis.
* **instruction** - Vysvětlení a oporučené akce.
* **category** - Detailní kategorizace výstrahy - pro více detailů čtěte v [návodu ČHMÚ](https://www.chmi.cz/files/portal/docs/meteo/om/vystrahy/doc/Dokumentace_CAP.pdf).

Ukázka vrácených hodnot, když není žádná výstraha:

```json
{
    "current_time": 1620736345,
    "events": []
}
```

a naopak když jsou výstrahy dvě:

```json
{
   "current_time":1620752111,
   "events":[
      {
         "text":"Mráz ve vegetačním období",
         "time_start_i":"2021-05-08T00:00:00+02:00",
         "time_end_i":"2021-05-08T08:00:00+02:00",
         "color":"yellow",
         "type":"low-temperature",
         "time_start_e":1620424800,
         "time_start_t":"so 8.5. 00:00",
         "time_end_e":1620453600,
         "time_end_t":"so 8.5. 08:00",
         "in_progress":"N"
      },
      {
         "text":"Silný vítr",
         "time_start_i":"2021-05-07T12:00:00+02:00",
         "time_end_i":"2021-05-07T21:00:00+02:00",
         "color":"yellow",
         "type":"Wind",
         "time_start_e":1620381600,
         "time_start_t":"pá 7.5. 12:00",
         "time_end_e":1620414000,
         "time_end_t":"pá 7.5. 21:00",
         "in_progress":"N"
      }
   ]
}
```

---
# Popis instalace

Potřebujete:

* webový server s podporou pro přepisování URL – tedy pro Apache httpd je potřeba zapnutý **mod_rewrite**
* rozumnou verzi PHP (nyní mám v provozu na 7.2)

Instalační kroky:

1) Stáhněte si celou serverovou aplikaci z githubu.

2) V adresáři vašeho webového serveru (nejčastěji něco jako /var/www/) udělejte adresář pro aplikaci, třeba "ChmiWarnings". Bude tedy existovat adresář /var/www/ChmiWarnings přístupný zvenčí jako https://vas-server/ChmiWarnings/ .

3) V konfiguraci webserveru (zde předpokládám Apache) povolte použití vlastních souborů .htaccess v adresářích aplikace – v nastavení /etc/apache2/sites-available/vaše-site.conf pro konkrétní adresář povolte AllowOverride

```
<Directory /var/www/ChmiWarnings/>
        AllowOverride all
        Order allow,deny
        allow from all
</Directory>
```

4) Nakopírujte obsah podadresáře aplikace/ do vytvořeného adresáře; vznikne tedy /var/www/ChmiWarnings/app ; /var/www/ChmiWarnings/data; ...

5) Přidělte webové aplikaci právo zapisovat do adresářů data, log a temp! Bez toho nebude nic fungovat. Nejčastěji by mělo stačit udělat v /var/www/ChmiWarnings/ něco jako:

```
chown www-data:www-data data log temp
chmod u+rwx data log temp
```

8) No a nyní zkuste v prohlížeči zadat https://vas-server/ChmiWarnings/chmi/vystrahy/5103 a měli byste dostat data.


## Řešení problémů, ladění a úpravy

Aplikace je napsaná v Nette frameworku. Pokud Nette neznáte, **důležitá informace**: Při úpravách aplikace či nasazování nové verze je třeba **smazat adresář temp/cache/** (tedy v návodu výše /var/www/ChmiWarnings/temp/cache). V tomto adresáři si Nette ukládá předkompilované šablony, mapování databázové struktury atd. Smazáním adresáře vynutíte novou kompilaci.

Aplikace **loguje** do adresáře log/ do souboru app.YYYY-MM-DD.txt . Defaultně zapisuje jen chyby; úroveň logování je možné změnit v app/Services/Logger.php v položce LOG_LEVEL.

Konfigurace aplikace je v app/Services/Config.php

Aplikace může být dle nastavení vašeho webserveru dostupná přes https nebo přes http (je jí to jedno).
