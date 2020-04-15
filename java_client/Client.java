import com.rabbitmq.client.Channel;
import com.rabbitmq.client.Connection;
import com.rabbitmq.client.ConnectionFactory;
import com.rabbitmq.client.DeliverCallback;
import mpi.MPI;
import mpi.MPIException;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.util.concurrent.TimeoutException;

class Client {

    protected long[] startTimeStamp = new long[1];
    protected String normalizedName;

    protected static final String CONTROL_QUEUE_NAME = "control_queue";
    protected ConnectionFactory factory;
    protected Connection connection;
    protected Channel channel;
    protected String consumerTag;

    private long startTime, endTime, duration;

    public static long getUnixTimestamp() {
        return System.currentTimeMillis() / 1000L;
    }

    /**
     * Notify test server that all queues have been created
     */
    protected void startExperiment(int contentLength, int repeats, int threads, int sleepTime) {
        System.out.println("Starting experiment");
        try {

            channel.queueDeclare(CONTROL_QUEUE_NAME, false, false, false, null);
            String message = "START:" + startTimeStamp[0] + ":" + contentLength + ":" + repeats + ":" + threads + ":" + sleepTime;
            channel.basicPublish("", CONTROL_QUEUE_NAME, null, message.getBytes());
            System.out.println(" [x] Sent '" + message + "'");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    protected void subscribeToQueue() throws IOException, TimeoutException, MPIException {
        factory = new ConnectionFactory();
        factory.setHost("68.183.37.164");
        connection = factory.newConnection();
        channel = connection.createChannel();
        channel.exchangeDeclare(MPI.getProcessorName(), "fanout", true);
        channel.queueDeclare(normalizedName, false, false, false, null);
        channel.queueBind(normalizedName, MPI.getProcessorName(), "");


        BufferedWriter writer = new BufferedWriter(
                new FileWriter("./results/" + startTimeStamp[0] + "/" + normalizedName + ".txt", true)  //Set true for append mode
        );

        System.out.println(" [*] Waiting for messages. To exit press CTRL+C");
        DeliverCallback deliverCallback = (consumerTag, delivery) -> {
            String message = new String(delivery.getBody(), "UTF-8");

            if (message.equals("FINAL_MESSAGE")) {
                channel.basicCancel(consumerTag);
                writer.close();
                try {
                    channel.close();
                } catch (TimeoutException e) {
                    e.printStackTrace();
                }
                connection.close();
            } else {
                writer.newLine();
                writer.write(message + "|" + java.lang.System.currentTimeMillis());
            }

            System.out.println(" [x] Received '" + message + "'");
        };
        consumerTag = channel.basicConsume(normalizedName, true, deliverCallback, consumerTag -> { });
    }

    protected void prepareResultsDir() {
        File f = null;
        boolean bool = false;

        try {
            // returns pathnames for files and directory
            f = new File("./results/");
            bool = f.mkdir();
            f = new File("./results/" + startTimeStamp[0]);
            // create
            bool = f.mkdir();


        } catch(Exception e) {
            // if any error occurs
            e.printStackTrace();
        }
    }

    protected void writeConfig(int contentLength, int repeats, int threads, int sleepTime) {
        try {
            BufferedWriter writer = new BufferedWriter(
                new FileWriter("./results/" + startTimeStamp[0] + "/config.json", true)  //Set true for append mode
            );

            writer.write("{\"timestamp\": " + startTimeStamp[0]+", \"iterations\": "+repeats+", \"sleep\": "+sleepTime+", \"threads\": "+threads+" \"length\" : "+contentLength+"}");

            writer.close();

        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public void prepareExperiment(int contentLength, int repeats, int sleepTime) throws MPIException, IOException, TimeoutException {
        int rank = MPI.COMM_WORLD.getRank(),
                size = MPI.COMM_WORLD.getSize();
        String node = MPI.getProcessorName();

        if (rank == 0) {
            startTimeStamp[0] = getUnixTimestamp(); //get only in root thread, in case thread generations takes longer
            //timestamp uniquely identifies an experiment run and should therefore be unique
        }

        MPI.COMM_WORLD.bcast(startTimeStamp, 1, MPI.INT, 0);
        normalizedName = "" + startTimeStamp[0] + "-" + String.format("%04d", rank) + "-" + String.format("%03d", 0);

        if (rank == 0) {
            prepareResultsDir();
            writeConfig(contentLength, repeats, size, sleepTime);
        }
        MPI.COMM_WORLD.barrier();
        subscribeToQueue();
        MPI.COMM_WORLD.barrier();


        if (rank == 0) {
            startExperiment(contentLength, repeats, size, sleepTime);
        }

        System.out.println(normalizedName + "@" + node);
    }

    public static void main(String args[]) throws MPIException, IOException, TimeoutException {
        MPI.Init(args);
        Client client = new Client();
        client.prepareExperiment(Integer.parseInt(args[0]), Integer.parseInt(args[1]), Integer.parseInt(args[2]));

        MPI.Finalize();
    }
}