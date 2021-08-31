<html>
  <head>
    <title>Using Redis Server with PHP and MySQL</title>
  </head> 
  <body>

    <h1 align = 'center'>Students' Register</h1>

    <table align = 'center' border = '2'>        

    <?php 
        try {

            $data_source = '';

            $redis = new Redis(); 
            $redis->connect('127.0.0.1', 6379); 

            $sql = 'select
                    student_id,
                    first_name,
                    last_name                                 
                    from student
                    ';

            $cache_key = md5($sql);

            if ($redis->exists($cache_key)) {

                $data_source = "Data from Redis Server";
                $data = unserialize($redis->get($cache_key));

            } else {

                $data_source = 'Data from MySQL Database';

                $db_name     = 'testdb';
                $db_user     = 'redisuser';
                $db_password = 'redispassword123';
                $db_host     = 'localhost';

                $pdo = new PDO('mysql:host=' . $db_host . '; dbname=' . $db_name, $db_user, $db_password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $data = []; 

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {          
                   $data[] = $row;  
                }  

                $redis->set($cache_key, serialize($data)); 
                $redis->expire($cache_key, 10);        
           }

           echo "<tr><td colspan = '3' align = 'center'><h2>$data_source</h2></td></tr>";
           echo "<tr><th>Student Id</th><th>First Name</th><th>Last Name</th></tr>";

           foreach ($data as $record) {
              echo '<tr>';
              echo '<td>' . $record['student_id'] . '</td>';
              echo '<td>' . $record['first_name'] . '</td>';
              echo '<td>' . $record['last_name']  . '</td>';                     
              echo '</tr>'; 
           }              


        } catch (PDOException $e) {
            echo 'Database error. ' . $e->getMessage();
        }
   ?>

    </table>
  </body>
</html>
