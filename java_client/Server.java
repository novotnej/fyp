import com.rabbitmq.client.Channel;
import com.rabbitmq.client.Connection;
import com.rabbitmq.client.ConnectionFactory;
import com.rabbitmq.client.DeliverCallback;
import com.rabbitmq.http.client.Client;
import com.rabbitmq.http.client.ClientParameters;
import com.rabbitmq.http.client.domain.ExchangeInfo;
import com.rabbitmq.http.client.domain.QueueInfo;

import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URISyntaxException;
import java.util.List;
import java.util.concurrent.TimeoutException;

public class Server {
    private static final String ALPHA_NUMERIC_STRING = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    protected static final String CONTROL_QUEUE_NAME = "control_queue";
    protected ConnectionFactory factory;
    protected Connection connection;
    protected Channel channel;

    public static long getUnixTimestamp() {
        return System.currentTimeMillis() / 1000L;
    }

    public static String randomAlphaNumeric(int count) {
        StringBuilder builder = new StringBuilder();
        while (count-- != 0) {
            int character = (int)(Math.random()*ALPHA_NUMERIC_STRING.length());
            builder.append(ALPHA_NUMERIC_STRING.charAt(character));
        }
        return builder.toString();
    }


    public void start() throws IOException, TimeoutException {
        factory = new ConnectionFactory();
        factory.setHost("rabbitmq.profisites.com");
        connection = factory.newConnection();
        channel = connection.createChannel();
        this.cleanupQueues();
        channel.queueDeclare(CONTROL_QUEUE_NAME, false, false, false, null);
        DeliverCallback deliverCallback = (consumerTag, delivery) -> {
            String message = new String(delivery.getBody(), "UTF-8");
            String[] args = message.split(":", 6);

            long startTimeStamp = Long.parseLong(args[1]);
            int contentLength = Integer.parseInt(args[2]);
            int repeats = Integer.parseInt(args[3]);
            int threads = Integer.parseInt(args[4]);
            int sleepTime = Integer.parseInt(args[5]);

            this.startExperiment(startTimeStamp, contentLength, repeats, threads, sleepTime);
            System.out.println(" [x] Received '" + message + "'");
        };
        channel.basicConsume(CONTROL_QUEUE_NAME, true, deliverCallback, consumerTag -> { });
    }

    protected void startExperiment(long startTimeStamp, int contentLength, int repeats, int threads, int sleepTime) {
        List<ExchangeInfo> exchanges = getDeclaredExchanges();
        exchanges.forEach(exchangeInfo -> {
            if (exchangeInfo.getType().equals("fanout") && !exchangeInfo.getName().equals("amq.fanout")) {
                //only create threads for fanout exchanges created by the clients
                ServerThread thread = null;
                try {
                    thread = new ServerThread(startTimeStamp, contentLength, repeats, threads, sleepTime, exchangeInfo.getName());
                } catch (IOException | TimeoutException e) {
                    e.printStackTrace();
                }
                if (thread != null) {
                    thread.start();
                }
            }
        });
    }

    protected List<ExchangeInfo> getDeclaredExchanges() {
        try {
            Client c = new Client(
                    new ClientParameters().url("http://rabbitmq.profisites.com:15672/api/").username("guest").password("guest")
            );
            return c.getExchanges();
        } catch (URISyntaxException | MalformedURLException e) {
            e.printStackTrace();
        }
        return null;
    }


    protected void cleanupQueues() {
        try {
            Client c = new Client(
                    new ClientParameters().url("http://rabbitmq.profisites.com:15672/api/").username("guest").password("guest")
            );
            List<QueueInfo> queues = c.getQueues();
            queues.forEach(queueInfo -> {
                if (!queueInfo.getName().equals(CONTROL_QUEUE_NAME)) {
                    c.deleteQueue(queueInfo.getVhost(), queueInfo.getName());
                }
            });
            List<ExchangeInfo> exchanges = c.getExchanges();
            exchanges.forEach(exchangeInfo -> {
                if (exchangeInfo.getType().equals("fanout") && !exchangeInfo.getName().equals("amq.fanout")) {
                    //delete fanout exchanges that are not the default fanout, hence probably created by the clients
                    c.deleteExchange(exchangeInfo.getVhost(), exchangeInfo.getName());
                }
            });

        } catch (URISyntaxException | MalformedURLException e) {
            e.printStackTrace();
        }
    }


    public static void main(String args[]) throws IOException, TimeoutException {
        Server server = new Server();
        server.start();
    }
}
