-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 17, 2025 at 03:13 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cozyshare`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `c_id` int(11) NOT NULL,
  `c_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`c_id`, `c_name`) VALUES
(1, 'Food Recipes'),
(2, 'Travel'),
(3, 'Book'),
(4, 'Movie'),
(5, 'My Spot');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` varchar(3000) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `comment`, `created_at`) VALUES
(81, 6, 1, 'x', '2025-07-17 14:32:56'),
(82, 6, 1, 'ascx', '2025-07-17 14:33:02'),
(83, 6, 1, 'x', '2025-07-17 14:34:19'),
(85, 5, 3, 'waw', '2025-07-17 15:08:02'),
(86, 3, 3, 'great', '2025-07-17 15:08:07');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `c_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `p_description` varchar(3000) NOT NULL,
  `p_image` varchar(255) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `c_id`, `u_id`, `title`, `p_description`, `p_image`, `created_at`) VALUES
(2, 1, 1, 'Renkli Ramen Bombası', 'Malzemeler:\\r\\n\\r\\n200 gr ramen eriştesi\\r\\n\\r\\n1 su bardağı doğranmış renkli biberler (kırmızı, sarı, yeşil)\\r\\n\\r\\n1 adet haşlanmış yumurta (ikiye bölünmüş)\\r\\n\\r\\n3 dal yeşil soğan\\r\\n\\r\\n1 diş sarımsak (ince kıyılmış)\\r\\n\\r\\n2 yemek kaşığı soya sosu\\r\\n\\r\\n1 yemek kaşığı susam yağı\\r\\n\\r\\n1 tatlı kaşığı acı biber sosu\\r\\n\\r\\n1 su bardağı sebze suyu veya tavuk suyu\\r\\n\\r\\nYapılışı:\\r\\n\\r\\nRamen eriştesini paketin üzerindeki talimatlara göre haşlayıp süzün.\\r\\n\\r\\nTavada susam yağını ısıtın, sarımsağı ekleyip kavurun.\\r\\n\\r\\nRenkli biberleri ekleyip birkaç dakika soteleyin.\\r\\n\\r\\nSoya sosu, acı sos ve sebze suyunu ilave edin.\\r\\n\\r\\nHaşlanmış erişteleri tavaya katın ve güzelce karıştırın.\\r\\n\\r\\nTabağa alın, üzerine haşlanmış yumurta ve doğranmış yeşil soğan ekleyin.\\r\\n\\r\\nSıcak servis yapın.\\r\\n\\r\\nAfiyet olsun!', 'food_686f95117218a9.59738429.jpg', '2025-07-10'),
(3, 2, 1, 'Kapadokya\\\'da Gün Doğumu', 'Kapadokya\\\'da geçirdiğim sabah saatleri hayatımın en huzurlu anlarından biriydi.\\r\\n\\r\\nGün doğarken gökyüzünü süsleyen sıcak hava balonları, altın renginde süzülen güneş ışığıyla birlikte masalsı bir manzara oluşturuyordu. Özellikle Göreme Vadisi\\\'nden izlemek, insanı bambaşka bir dünyaya götürüyor.\\r\\n\\r\\nSabahın serinliği, hafif rüzgar ve taş evlerin arasından gelen sessizlikle birleşince, sadece izlemek yetiyor. Eğer erken uyanmayı dert etmiyorsanız, bu deneyimi kesinlikle yaşamalısınız.\\r\\n\\r\\nMutlaka bir termosla sıcak kahve alın ve gün doğumunu bekleyin. 💛\\r\\n\\r\\nAfiyet değil ama... huzur olsun :)', 'img_686fb572914982.45207901.jpg', '2025-07-10'),
(5, 4, 1, 'Chernobyl', '2019 yapımı bu mini dizi, 1986 yılında Ukrayna’nın Pripyat kentindeki Çernobil Nükleer Santrali’nde meydana gelen patlamayı konu alıyor.\\r\\n\\r\\nGerçek olaylara dayanıyor ve insan hatasının nelere mal olabileceğini tüm çıplaklığıyla gözler önüne seriyor. Sovyet bürokrasisi, bilim insanları, itfaiyeciler ve halk üzerinden işlenen dramatik anlatımıyla hem ürkütücü hem de derinlemesine öğretici.\\r\\n\\r\\nToplam 5 bölümden oluşmasıyla kısa sürede izlenebilir ama etkisi uzun süre kalıyor. Oyunculuk, atmosfer ve müzikler gerçekten mükemmel. Özellikle gerilim türünü sevenler için tam bir başyapıt.\\r\\n\\r\\n📺 IMDb: 9.4  \\r\\n🎬 Tür: Tarihi, Drama, Gerilim', 'movie_686fb6c54449e6.47026311.jpg', '2025-07-10'),
(6, 5, 1, 'Kitaplığın Yanındaki Köşe', 'Bazen dışarı çıkmadan huzuru bulmak mümkün.  \\r\\nEvimde en sevdiğim yer, kitaplığın hemen yanındaki pencere kenarı. Oraya küçük bir berjer koydum, yanına da yumuşak sarı ışıklı bir lambader. Battaniyem her zaman koltuğun kenarında hazır. \\r\\n\\r\\nPencerenin dışı genelde sessiz; kuş sesleriyle dolu. Çayımı ya da kahvemi alıp oturduğumda zaman yavaşlıyor gibi hissediyorum. Bu köşe benim kaçış noktam oldu. Kitap okumak, günlüğe birkaç satır karalamak veya sadece dalıp gitmek için birebir.\\r\\n\\r\\nHer sabah 15 dakika burada geçirdiğimde, gün çok daha dengeli başlıyor. Belki de \\\"cozy\\\" olmak için uzaklara gitmeye gerek yoktur...\\r\\n\\r\\n📍Konum: Evdeki kitap köşesi  \\r\\n☕ Eşlikçiler: Sıcak içecek, kitap ve biraz sessizlik  \\r\\n🌤️ Günün en güzel saati: Sabah 08:00 civarı', 'spot_686fb79f757a41.95516978.jpg', '2025-07-10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `u_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `u_role` enum('admin','user') DEFAULT 'user',
  `profile_image` varchar(255) DEFAULT NULL,
  `about` varchar(3000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`u_id`, `username`, `password`, `email`, `gender`, `u_role`, `profile_image`, `about`) VALUES
(1, 'Kerem', '$2y$10$Tv8hrrVeTYmFSAxs1nKyaeoo4R.6.z4azFTUnROrKdn4hiCYuejX6', 'kerem@example.com', 'male', 'admin', '1686f9495c40bf5.07843230.jpg', 'asdadsadsadsadsadsasddasadsadsadsadsadsadsadsadsadsdasdsadsadsadsadssadadsasdadsads'),
(3, 'Jane', '$2y$10$mV1BMcpNGSSunT5FxHCcRediyVAJliFd.O5iDZbJPocHF7iQGQGhm', 'jane@example.com', 'female', 'user', '3686fa6f9f14b49.56417635.jpg', ''),
(4, 'cozylover', '$2y$10$V/T4yUyFaNnwftaYWxTxIuNNMEEUO11JUhFn0/IoQttl2tY.4P02i', 'cozy@example.com', 'male', 'user', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote` tinyint(4) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `post_id`, `user_id`, `vote`, `created_at`) VALUES
(17, 2, 3, 1, '2025-07-10'),
(23, 6, 1, 1, '2025-07-11'),
(25, 2, 1, 1, '2025-07-17'),
(28, 6, 3, 1, '2025-07-17'),
(29, 5, 3, 1, '2025-07-17'),
(30, 3, 3, 1, '2025-07-17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`c_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `c_id` (`c_id`,`u_id`),
  ADD KEY `u_id` (`u_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `c_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`u_id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `users` (`u_id`),
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`c_id`) REFERENCES `category` (`c_id`);

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`u_id`),
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
