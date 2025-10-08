<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    'accepted' => ':attribute muss akzeptiert werden.',
    'accepted_if' => ':attribute muss akzeptiert werden, wenn :other :value ist.',
    'active_url' => ':attribute ist keine gültige URL.',
    'alpha_dash' => ':attribute darf nur Buchstaben, Zahlen, Bindestriche und Unterstriche enthalten.',
    'alpha_num' => ':attribute darf nur Buchstaben und Zahlen enthalten.',
    'ascii' => ':attribute darf nur einbyteige alphanumerische Zeichen und Symbole enthalten.',
    'after' => ':attribute muss nach :date liegen.',
    'after_or_equal' => ':attribute muss nach oder am :date liegen.',
    'array' => ':attribute muss ein Array sein.',
    'before' => ':attribute muss vor :date liegen.',
    'boolean' => ':attribute muss wahr oder falsch sein.',
    'can' => ':attribute enthält einen nicht autorisierten Wert.',
    'confirmed' => 'Die :attribute-Bestätigung stimmt nicht überein.',
    'contains' => ':attribute fehlt ein erforderlicher Wert.',
    'current_password' => 'Das Passwort ist falsch.',
    'date' => ':attribute muss ein gültiges Datum sein.',
    'date_equals' => ':attribute muss ein Datum sein, das :date entspricht.',
    'date_format' => ':attribute muss dem Format :format entsprechen.',
    'decimal' => ':attribute muss :decimal Dezimalstellen haben.',
    'declined' => ':attribute muss abgelehnt werden.',
    'declined_if' => ':attribute muss abgelehnt werden, wenn :other :value ist.',
    'different' => ':attribute und :other müssen unterschiedlich sein.',
    'digits' => ':attribute muss :digits Ziffern lang sein.',
    'digits_between' => ':attribute muss zwischen :min und :max Ziffern lang sein.',
    'dimensions' => ':attribute hat ungültige Bildmaße.',
    'distinct' => ':attribute hat einen doppelten Wert.',
    'doesnt_end_with' => ':attribute darf nicht mit einem der folgenden Werte enden: :values.',
    'doesnt_start_with' => ':attribute darf nicht mit einem der folgenden Werte beginnen: :values.',
    'email' => ':attribute muss eine gültige E-Mail-Adresse sein.',
    'ends_with' => ':attribute muss mit einem der folgenden Werte enden: :values.',
    'enum' => 'Das ausgewählte :attribute ist ungültig.',
    'exists' => 'Das ausgewählte :attribute ist ungültig.',
    'extensions' => ':attribute muss eine der folgenden Erweiterungen haben: :values.',
    'file' => ':attribute muss eine Datei sein.',
    'filled' => ':attribute muss einen Wert haben.',
    'gt' => [
        'array' => ':attribute muss mehr als :value Einträge haben.',
        'file' => ':attribute muss größer als :value Kilobytes sein.',
        'numeric' => ':attribute muss größer als :value sein.',
        'string' => ':attribute muss mehr als :value Zeichen enthalten.',
    ],
    'gte' => [
        'array' => ':attribute muss mindestens :value Einträge haben.',
        'file' => ':attribute muss mindestens :value Kilobytes groß sein.',
        'numeric' => ':attribute muss mindestens :value sein.',
        'string' => ':attribute muss mindestens :value Zeichen enthalten.',
    ],
    'hex_color' => ':attribute muss eine gültige hexadezimale Farbe sein.',
    'image' => ':attribute muss ein Bild sein.',
    'immutable' => ':attribute darf nicht mehr verändert werden',
    'in' => 'Das ausgewählte :attribute ist ungültig.',
    'in_array' => ':attribute muss in :other existieren.',
    'integer' => ':attribute muss eine ganze Zahl sein.',
    'ip' => ':attribute muss eine gültige IP-Adresse sein.',
    'ipv4' => ':attribute muss eine gültige IPv4-Adresse sein.',
    'ipv6' => ':attribute muss eine gültige IPv6-Adresse sein.',
    'json' => ':attribute muss ein gültiger JSON-String sein.',
    'list' => ':attribute muss eine Liste sein.',
    'lowercase' => ':attribute muss klein geschrieben sein.',
    'lt' => [
        'array' => ':attribute muss weniger als :value Einträge haben.',
        'file' => ':attribute muss kleiner als :value Kilobytes sein.',
        'numeric' => ':attribute muss kleiner als :value sein.',
        'string' => ':attribute muss weniger als :value Zeichen enthalten.',
    ],
    'lte' => [
        'array' => ':attribute darf nicht mehr als :value Einträge haben.',
        'file' => ':attribute darf nicht mehr als :value Kilobytes groß sein.',
        'numeric' => ':attribute darf nicht mehr als :value sein.',
        'string' => ':attribute darf nicht mehr als :value Zeichen enthalten.',
    ],
    'mac_address' => ':attribute muss eine gültige MAC-Adresse sein.',
    'max_digits' => ':attribute darf nicht mehr als :max Ziffern haben.',
    'min' => [
        'array' => ':attribute muss mindestens :min Einträge haben.',
        'file' => ':attribute muss mindestens :min Kilobytes groß sein.',
        'numeric' => ':attribute muss mindestens :min sein.',
        'string' => ':attribute muss mindestens :min Zeichen enthalten.',
    ],
    'mimes' => ':attribute muss eine Datei im Format :values sein.',
    'mimetypes' => ':attribute muss eine Datei im Format :values sein.',
    'min_digits' => ':attribute muss mindestens :min Ziffern haben.',
    'missing' => ':attribute muss fehlen.',
    'missing_if' => ':attribute muss fehlen, wenn :other :value ist.',
    'missing_unless' => ':attribute muss fehlen, es sei denn, :other ist :value.',
    'missing_with' => ':attribute muss fehlen, wenn :values vorhanden ist.',
    'missing_with_all' => ':attribute muss fehlen, wenn :values vorhanden sind.',
    'multiple_of' => ':attribute muss ein Vielfaches von :value sein.',
    'not_in' => 'Das ausgewählte :attribute ist ungültig.',
    'not_regex' => 'Das Format von :attribute ist ungültig.',
    'numeric' => ':attribute muss eine Zahl sein.',
    'password' => [
        'letters' => ':attribute muss mindestens einen Buchstaben enthalten.',
        'mixed' => ':attribute muss mindestens einen Großbuchstaben und einen Kleinbuchstaben enthalten.',
        'numbers' => ':attribute muss mindestens eine Zahl enthalten.',
        'symbols' => ':attribute muss mindestens ein Symbol enthalten.',
        'uncompromised' => 'Das angegebene :attribute wurde in einem Datenleck gefunden. Bitte wähle ein anderes :attribute.',
    ],
    'present' => ':attribute muss vorhanden sein.',
    'present_if' => ':attribute muss vorhanden sein, wenn :other :value ist.',
    'present_unless' => ':attribute muss vorhanden sein, es sei denn :other ist :value.',
    'present_with' => ':attribute muss vorhanden sein, wenn :values vorhanden sind.',
    'present_with_all' => ':attribute muss vorhanden sein, wenn :values vorhanden sind.',
    'prohibited' => ':attribute ist verboten.',
    'prohibited_if' => ':attribute ist verboten, wenn :other :value ist.',
    'prohibited_unless' => ':attribute ist verboten, es sei denn :other ist in :values enthalten.',
    'prohibits' => ':attribute verbietet das Vorhandensein von :other.',
    'regex' => 'Das Format von :attribute ist ungültig.',
    'required' => ':attribute ist erforderlich.',
    'required_array_keys' => ':attribute muss Einträge für :values enthalten.',
    'required_if' => ':attribute ist erforderlich, wenn :other :value ist.',
    'required_if_accepted' => ':attribute ist erforderlich, wenn :other akzeptiert wurde.',
    'required_if_declined' => ':attribute ist erforderlich, wenn :other abgelehnt wurde.',
    'required_unless' => ':attribute ist erforderlich, es sei denn :other ist in :values enthalten.',
    'required_with' => ':attribute ist erforderlich, wenn :values vorhanden ist.',
    'required_with_all' => ':attribute ist erforderlich, wenn :values vorhanden sind.',
    'required_without' => ':attribute ist erforderlich, wenn :values nicht vorhanden ist.',
    'required_without_all' => ':attribute ist erforderlich, wenn keine der Werte :values vorhanden sind.',
    'same' => ':attribute und :other müssen übereinstimmen.',
    'starts_with' => ':attribute muss mit einem der folgenden Werte beginnen: :values.',
    'string' => ':attribute muss ein Text sein.',
    'timezone' => ':attribute muss eine gültige Zeitzone sein.',
    'unique' => ':attribute ist bereits vergeben.',
    'uploaded' => 'Der Upload von :attribute ist fehlgeschlagen.',
    'uppercase' => ':attribute muss in Großbuchstaben sein.',
    'url' => ':attribute muss eine gültige URL sein.',
    'ulid' => ':attribute muss eine gültige ULID sein.',
    'uuid' => ':attribute muss eine gültige UUID sein.',


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'Name',
        'abbreviation' => 'Abkürzung',
        'type' => 'Typ',
        'requires_approval' => 'Muss genehmigt werden',
        'users' => 'Mitarbeiter',
        'address_suffix' => 'Adresszusatz',
        'city' => 'Stadt',
        'country' => 'Land',
        'email' => 'E-Mail Adresse',
        'fax' => 'Fax',
        'federal_state' => 'Bundesland',
        'house_number' => 'Hausnummer',
        'phone_number' => 'Telefonnummer',
        'street' => 'Straße',
        'zip' => 'PLZ',
        'start' => 'Start',
        'end' => 'Ende',
        'first_name' => 'Vorname',
        'last_name' => 'Nachname',
        'password' => 'Passwort',
        'date_of_birth' => 'Geburtsdatum',
        'permissions' => 'Berechtigungen',
        'userWorkingHours' => 'Wöchentliche Arbeitszeit',
        'userWorkingWeek' => 'Arbeitstage des Mitarbeiters',
        'active_since' => 'Aktiv seit',
        'user_working_weeks.*.weekdays' => 'Beschäftigungstage',
        'user_working_hours.*.weekly_working_hours' => 'Stunden pro Woche',
        'year' => 'Jahr',
        'month' => 'Monat',
        'description' => 'Beschreibung',
        'title' => 'Betreff',
        'resignation_date' => 'Kündigungsdatum',
        'set_name' => 'Filtername',
        'extra_charge' => 'Faktor Zuschlag',
        'customer_id' => 'Kunde',
        'tab' => 'Auswahl',
        'duration' => 'Dauer',
        'priority' => 'Priorität',
        'resources' => 'Ressourcen',
        'assignees' => 'Zuständige',
        'ticket' => 'Ticket',
        'record' => 'Eintrag',
        'files' => 'Dateien',
        'file' => 'Datei',
    ],

    'values' => [
        'tab' => [
            'expressTicket' => 'Einzelauftrag'
        ]
    ]

];
