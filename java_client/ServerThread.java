import com.rabbitmq.client.Channel;
import com.rabbitmq.client.Connection;
import com.rabbitmq.client.ConnectionFactory;

import java.io.IOException;
import java.time.Instant;
import java.util.concurrent.TimeoutException;

public class ServerThread extends Thread {
    private static final String ALPHA_NUMERIC_STRING = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    protected static final String CONTROL_QUEUE_NAME = "control_queue";
    protected ConnectionFactory factory;
    protected Connection connection;
    protected Channel channel;

    public static long getUnixTimestamp() {
        return System.currentTimeMillis() / 1000L;
    }


    private int contentLength, repeats, threads, sleepTime;
    private long startTimeStamp, startTime, endTime, duration;
    private String queueName;

    public static String randomAlphaNumeric(int count) {
        StringBuilder builder = new StringBuilder();
        while (count-- >= 0) {
            int character = (int)(Math.random()*ALPHA_NUMERIC_STRING.length());
            builder.append(ALPHA_NUMERIC_STRING.charAt(character));
        }
        return builder.toString();
    }

    @Override
    public void run() {
        System.out.println("Q: " + queueName + ", REP: " + 0);
        for (int i = 0; i < repeats; i++) {
            //System.out.println("Q: " + queueName + ", REP: " + i);
            startTime = java.lang.System.currentTimeMillis();
            String randomMessage = randomAlphaNumeric(contentLength);
            endTime = java.lang.System.currentTimeMillis();
            duration = endTime - startTime;
            String message = queueName + "|" + startTime + "|" + endTime + "|" + duration + "|" + Instant.now().getEpochSecond();
            try {
                channel.basicPublish("", queueName, null, message.getBytes());
            } catch (IOException e) {
                e.printStackTrace();
            }
            try {
                Thread.sleep(sleepTime / 1000); //sleeptime is in microseconds
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }
        this.terminateExperiment();
    }

    protected void terminateExperiment() {
        String message = "FINAL_MESSAGE";
        try {
            channel.basicPublish("", queueName, null, message.getBytes());
            Thread.sleep(1000); //wait for a second so that the messages are delivered before deleting the queue
            channel.queueDelete(queueName);
        } catch (IOException | InterruptedException e) {
            e.printStackTrace();
        }
    }

    public ServerThread(long startTimeStamp, int contentLength, int repeats, int threads, int sleepTime, String queueName) throws IOException, TimeoutException {
        this.startTimeStamp = startTimeStamp;
        this.contentLength = contentLength;
        this.repeats = repeats;
        this.threads = threads;
        this.sleepTime = sleepTime;
        this.queueName = queueName;
        factory = new ConnectionFactory();
        factory.setHost("rabbitmq.profisites.com");
        connection = factory.newConnection();
        channel = connection.createChannel();
    }
}
