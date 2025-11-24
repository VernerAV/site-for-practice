<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ГБУ Жилищник района Строгино</title>
</head>
<body>
    <!-- Заголовок -->
    <header>
        <div class="icon"><img src="img/icon" alt="icon"></div>
        <div class="name"><h1>ГБУ "Жилищник Района Строгино"</h1></div>
        <div id="search">
            <form action="search.php" method="get">
                <input type="text" name="query" placeholder="График отключения горячей воды">
                <button type="submit"><img src="img/search" alt="search"></button>
            </form>
        </div>
        <div class="enter"><a href="enter.php"></a></div>
    </header>
<!-- меню -->
   <div class="menu-container">
    <nav class="main-menu">
        <ul>
            <li><a href="news.php">Новости</a></li>
            <li><a href="price.php">Платные услуги</a></li>
            <li><a href="about.php">О нас</a></li>
            <li><a href="user.php">Личный кабинет</a></li>
        </ul>
    </nav>
    
    <div class="contact-info">
        <div class="phone">Телефон: 8(495) 758-38-22</div>
        <div class="email">Эл. почта: gbu-strogino@mail.ru</div>
    </div>
</div>
<!-- Основная страница -->
    <main>
        <div class="banner1">
            <div class="left">
                <h1>У вас проблема?</h1>
                <p>Оставьте заявку на ремонт онлайн — мы исправим!</p>
                <a href="">Оставить заявку</a>
            </div>

            <div class="right">
                <h1>ЧТО-ТО СЛОМАЛОСЬ?</h1>
                <p>Проблемы с водой, лифтами, отоплением? Сообщите в диспетчерскую!</p>
                <p class="tel">8 (495) 539-53-53</p>
                <p class="light"> Круглосуточно • Примем заявку на ремонт • Ответим на вопросы</p>
            </div>
        </div>
        <!-- Таблица график приема -->
        <div class="schedule">
            <table class="iksweb">
		<tr>
			<td></td>
			<td class="grey">Директор</td>
			<td>Первый заместитель, заместитель директора</td>
			<td class="grey">Главный инженер</td>
			<td>Начальники участков учреждения</td>
		</tr>
		<tr>
			<td>Понедельник</td>
			<td class="grey"></td>
			<td>17:00 - 20:00</td>
			<td class="grey"></td>
			<td></td>
		</tr>
		<tr>
			<td>Вторник</td>
			<td class="grey">16:00 - 19:00</td>
			<td></td>
			<td class="grey">17:00 - 20:00</td>
			<td>17:00 - 20:00</td>
		</tr>
		<tr>
			<td>Среда</td>
			<td class="grey"></td>
			<td></td>
			<td class="grey"></td>
			<td></td>
		</tr>
		<tr>
			<td>Четверг</td>
			<td class="grey">17:00 - 20:00</td>
			<td></td>
			<td class="grey"></td>
			<td></td>
		</tr>
		<tr>
			<td>Пятница</td>
			<td class="grey"></td>
			<td>16:00 - 19:00</td>
			<td class="grey"></td>
			<td></td>
		</tr>
		<tr>
			<td>Суббота</td>
			<td class="grey"></td>
			<td></td>
			<td class="grey">10:00 - 14:00</td>
			<td>10:00 - 14:00</td>
		</tr>
            </table>
        </div>

        <div class="news">
            <div class="block_news">
                <div class="block">
                    <img src="" alt="">
                    <h1></h1>
                    <p></p>
                    <p class="time"></p>
                </div>
            </div>
            <div class="rectangle"></div>
        </div>
    </main>
    
    <footer></footer>
</body>
</html>