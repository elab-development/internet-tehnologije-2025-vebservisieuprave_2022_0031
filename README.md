# MojaEUprava
MojaEUprava je web aplikacija koja omogućava građanima da elektronski podnose zahteve za administrativne usluge i zakazuju termine bez potrebe za odlaskom na šalter. Cilj aplikacije je da pojednostavi komunikaciju između građana i državnih institucija i da omogući bržu i efikasniju obradu zahteva.

Aplikacija podržava različite tipove korisnika i omogućava podnošenje zahteva, zakazivanje termina, upravljanje profilom korisnika i administrativnu obradu zahteva.



## Sta je potrebno instalirati
Da bi aplikacija mogla da se pokrene, potrebno je instalirati sledece alate:
- Docker Destop- omogucava pokretanje baze, backenda i frontenda bez rucne instalacije PHP-a, MySQL-a i Node-a

- Git- Koristi se za kloniranje i reprozitorijum i verzionisanje koda

## Kako preuzeti projekat
1. Kloniranje reprozitorijuma
U terminalu pokrenuti: 
            
            git clone [<URL_REPOZITORIJUMA>](https://github.com/elab-development/internet-tehnologije-2025-vebservisieuprave_2022_0031.git)
            cd <NAZIV_PROJEKTA>
            
2. Pokretanje aplikacije preko Dockera
U root folderu projekta (gde se nalazi docker-compose.yml) u terminalu pokrenuti:
    
        docker compose up -d --build
        
3. Podesavanje Laravel aplikacije
U folderu backend podesiti .env fajl (ako ne postoji kopirati iz .env example) i postaviti sledeca podesavanje za DB_*
                
                DB_CONNECTION=mysql
                DB_HOST=db
                DB_PORT=3306
                DB_DATABASE= app_db
                DB_USERNAME=app_user
                DB_PASSWORD=app_pass
                
Zatim pokrenuti: 
            
            docker compose exec app composer install
            docker compose exec app php artisan config:clear
            docker compose exec app php artisan migrate

            docker compose exec app php artisan db:seed
            
4. Pristup aplikaciji
Nakon uspesnog pokretanja app ce biti dostupne na sledecim linkovima: 

                [text](http://127.0.0.1:8000/)
                [text](http://localhost:3000/)

## Funkcionalnosti aplikacije

###Autentifikacija

Korisnici mogu:

-da se registruju kao domaći ili strani državljanin

-da se prijave na sistem (login)

-da se odjave sa sistema (logout)

