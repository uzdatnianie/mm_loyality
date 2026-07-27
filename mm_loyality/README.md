# MM Loyalty

Moduł Loyalty Points dla Drupal 11 i Commerce 3. Umożliwia przyznawanie punktów lojalnościowych za zakupy, komentarze i oceny produktów, a także śledzenie historii punktów oraz wymianę punktów na nagrody.

## Funkcje modułu

- przyznawanie punktów za zakupy po zrealizowaniu zamówienia,
- przyznawanie punktów za dodanie komentarza do produktu,
- przyznawanie punktów za wystawienie oceny produktu,
- wyświetlanie salda punktów użytkownika,
- historia transakcji punktów,
- lista nagród i mechanizm ich odbioru,
- panel administracyjny do zarządzania punktami i historią.

## Wymagania

- Drupal 11,
- Commerce 3,
- moduły: Commerce, User, Comment, Rating, System.

## Instalacja

1. Skopiuj folder modułu do katalogu `modules/custom` lub `modules/contrib`.
2. Włącz moduł wraz z zależnościami.
3. Przejdź do panelu administracyjnego i skonfiguruj ustawienia modułu.

Przykład przy użyciu Drush:

```bash
drush en mm_loyality commerce user comment rating system -y
```

## Konfiguracja

Po aktywacji modułu dostępne są ustawienia pod ścieżką:

- `/admin/commerce/loyalty`

Ustawienia obejmują:

- wartość punktu w PLN,
- liczbę punktów za komentarz,
- liczbę punktów za ocenę,
- włączenie lub wyłączenie punktów za zakupy, komentarze i oceny.

## Jak działa moduł

- Po zakończeniu zamówienia moduł może dodać punkty za zakup.
- Po dodaniu komentarza użytkownik otrzymuje punkty, jeśli funkcja jest aktywna.
- Po dodaniu oceny użytkownik otrzymuje punkty, jeśli funkcja jest aktywna.
- Wszystkie zdarzenia są zapisywane jako transakcje punktów.

## Struktura modułu

- `src/Controller` – kontrolery stron użytkownika i administratora,
- `src/Form` – formularze konfiguracyjne,
- `src/Entity` – encje typu Reward, Transaction i Redemption,
- `src/EventSubscriber` – subskrybenci zdarzeń dla zakupów, komentarzy i ocen,
- `src/Plugin/Block` – blok z aktualnym saldem punktów,
- `src/Service` – logika biznesowa modułu.

## Kluczowe usługi

- `LoyaltyManager` – zarządzanie punktami i operacjami biznesowymi,
- `PointsCalculator` – obliczanie liczby punktów na podstawie ustawień,
- `TransactionManager` – zapis i odczyt transakcji punktów,
- `RewardManager` – obsługa nagród i ich wykupu.

## Zasoby i ścieżki

- `/admin/commerce/loyalty` – ustawienia modułu,
- `/admin/commerce/loyalty/users` – lista użytkowników z punktami,
- `/admin/commerce/loyalty/users/{user}/history` – historia punktów użytkownika,
- `/loyalty/rewards` – lista nagród,
- `/loyalty/rewards/{reward}/exchange` – wymiana punktów na nagrodę,
- `/user/{user}/loyalty` – własne saldo punktów.

## Uprawnienia

- `administer loyalty`
- `view loyalty points`
- `manage loyalty points`
- `view all loyalty history`

## Uwagi

Moduł jest gotowy do podstawowej instalacji i konfiguracji, ale warto uzupełnić go o testy automatyczne oraz dodatkową dokumentację dla procesów biznesowych w środowisku produkcyjnym.
