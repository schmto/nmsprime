<?php

return [
    'groupContract' => [
        'item' => 'Der Vertrag gehört zu einem Gruppenvertrag. TV-Gebühren werden tendenziell über den Gruppenvertrag abgerechnet.',
        'modem' => 'Der Vertrag ist ein Gruppenvertrag und dient nur der Abrechnung der TV-Gebühren. Für diesen Vertrag können keine Modems angelegt werden.',
        'probably' => 'Der Vertrag könnte zu einem Gruppenvertrag gehören. Bitte vergewissern Sie sich ob die TV-Gebühren über den Gruppenvertrag der Hausverwaltung abgerechnet werden.',
    ],
    'invoice' => [
        'invalidRealtyHousenr' => 'Die Liegenschaft mit ID :id hat eine fehlerhafte Hausnummer (:nr) und kann deshalb nicht zur Objektliste des Vertrages :contractnr hinzugefügt werden.',
    ],
    'realty' => [
        'apartmentRelationInfo' => 'Wohnungen können der Liegenschaft nur zugewiesen werden, wenn dies ein Mehrfamilienhaus ist und Modems nicht direkt hinzugefügt wurden. Löschen Sie alle zugewiesenen Modems, wenn Sie der Liegenschaft Wohnungen zuweisen wollen.',
        'modemRelationInfo' => 'Modems können der Liegenschaft nur explizit zugewiesen werden, wenn sie keinen Gruppenvertrag enthält und dies ein Einfamilienhaus ist - also keine Wohnungen angelegt wurden. Löschen Sie alle zugewiesenen Wohnungen, wenn Sie der Liegenschaft direkt Modems zuweisen wollen.',
    ],
    'tvContract' => 'Dem Vertrag können keine Modems zugewiesen werden, da er direkt einer Wohnung zugeordnet ist und damit nur der Abrechnung der Posten dient.',
];
