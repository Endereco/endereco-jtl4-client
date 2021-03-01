CREATE TABLE IF NOT EXISTS `xplugin_endereco_jtl4_client_tams` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`kKunde` INT(10) unsigned DEFAULT NULL,
		`kRechnungsadresse` INT(10) unsigned DEFAULT NULL,
		`kLieferadresse` INT(10) unsigned DEFAULT NULL,
		`enderecoamsts` INT NOT NULL,
		`enderecoamsstatus` TEXT NOT NULL,
		`enderecoamspredictions` TEXT NOT NULL,
		`last_change_at` timestamp NOT NULL DEFAULT NOW()
);
ALTER TABLE `xplugin_endereco_jtl4_client_tams`
ADD UNIQUE `kKunde` (`kKunde`);
ALTER TABLE `xplugin_endereco_jtl4_client_tams`
ADD UNIQUE `kRechnungsadresse` (`kRechnungsadresse`);
ALTER TABLE `xplugin_endereco_jtl4_client_tams`
ADD UNIQUE `kLieferadresse` (`kLieferadresse`);
