SELECT t.departement_id, t.categorie_id, c.type, c.nature, SUM(t.montant) as montant
        FROM transactions t
        JOIN categories c ON t.categorie_id = c.id
        WHERE t.periode_id = 1
        GROUP BY t.departement_id, t.categorie_id;


        INSERT INTO soldes (departement_id, periode_id, solde_debut, solde_fin) VALUES
(1, 1, 50000000.00, NULL);