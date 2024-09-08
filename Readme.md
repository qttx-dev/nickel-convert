# Nickel Kontoauszug-Verarbeitung

Verarbeite und extrahiere Transaktionen aus Nickel-Kontoauszügen im PDF-Format. Dieses Projekt ermöglicht es dir, Kontoauszüge hochzuladen, Transaktionen zu überprüfen und zu bearbeiten sowie die bearbeiteten Daten im CSV-Format herunterzuladen.

## Funktionen

- **PDF-Verarbeitung:** Extrahiere Transaktionen aus Nickel-Kontoauszügen im PDF-Format.
- **Transaktionsbearbeitung:** Bearbeite Transaktionen direkt in der Web-Oberfläche.
- **CSV-Export:** Exportiere die bearbeiteten Transaktionen als CSV-Datei.
- **Echtzeit-Feedback:** Speichern von Änderungen mit sofortigem visuellen Feedback.
- **UTF-8 und Semikolon-Trennzeichen:** CSV-Dateien werden in UTF-8 kodiert und verwenden Semikolon als Trennzeichen.

## Installation

### Voraussetzungen

- PHP 7.4 oder höher
- Composer
- Webserver (z.B. Apache, Nginx)

### Schritt-für-Schritt-Anleitung

1. **Repository Klonen**

   ```bash
   git clone https://github.com/qttx-dev/nickel-convert.git
   cd nickel-convert
   ```

2. **Abhängigkeiten Installieren**

   Stelle sicher, dass Composer installiert ist und führe den folgenden Befehl aus:

   ```bash
   composer install
   ```

3. **Webserver Konfigurieren**

   Konfiguriere deinen Webserver, um das Projektverzeichnis zu bedienen. Stelle sicher, dass der Webserver auf das Verzeichnis zugreifen kann, in dem du die Anwendung installiert hast.

4. **PDF Parser Abhängigkeit**

   Das Projekt verwendet `smalot/pdfparser`. Dies sollte bereits durch Composer installiert worden sein. Wenn nicht, kannst du es mit dem folgenden Befehl hinzufügen:

   ```bash
   composer require smalot/pdfparser
   ```

5. **Dateiberechtigungen**

   Stelle sicher, dass die Verzeichnisse, in denen temporäre Dateien gespeichert werden (z.B. Upload-Verzeichnis), die richtigen Berechtigungen haben, damit die Webanwendung Dateien speichern und lesen kann.

## Nutzung

1. **Kontoauszug Hochladen**

   Gehe zur Hauptseite der Anwendung und lade deine Nickel-PDF-Kontoauszüge hoch. Die Anwendung wird die Transaktionen extrahieren und anzeigen.

2. **Transaktionen Bearbeiten**

   Bearbeite die extrahierten Transaktionen direkt in der Tabelle. Du kannst den Zahlungsempfänger, die IBAN und den Verwendungszweck ändern.

3. **Änderungen Speichern**

   Klicke auf "Speichern" neben jeder Zeile oder auf "Alle Änderungen speichern", um alle bearbeiteten Zeilen zu speichern.

4. **CSV exportieren**

   Nach dem Bearbeiten kannst du die Daten als CSV-Datei exportieren, indem du auf "CSV herunterladen" klickst.

## Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert. Siehe die [LICENSE](LICENSE) Datei für weitere Details.