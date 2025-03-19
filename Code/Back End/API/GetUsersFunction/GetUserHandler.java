package com.fitify.aws.lambda;

import com.amazonaws.services.lambda.runtime.Context;
import com.amazonaws.services.lambda.runtime.RequestHandler;
import com.amazonaws.services.lambda.runtime.events.APIGatewayProxyRequestEvent;
import com.amazonaws.services.lambda.runtime.events.APIGatewayProxyResponseEvent;
import com.google.gson.JsonObject;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.util.Map;

public class GetUserHandler implements RequestHandler<APIGatewayProxyRequestEvent, APIGatewayProxyResponseEvent> {

    //database connection details
    private static final String URL = "jdbc:mysql://fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com:3306/fitifyDB";
    private static final String USERNAME = "root"; //RDS username
    private static final String PASSWORD = "fitify123"; //RDS password

    @Override
    public APIGatewayProxyResponseEvent handleRequest(APIGatewayProxyRequestEvent input, Context context) {
        Map<String, String> pathParameters = input.getPathParameters();
        String userId = (pathParameters != null) ? pathParameters.get("user-id") : null;

        APIGatewayProxyResponseEvent response = new APIGatewayProxyResponseEvent();
        JsonObject returnValue = new JsonObject();

        if (userId == null) {
            returnValue.addProperty("error", "Missing 'user-id' in request path parameters");
            return response.withStatusCode(400).withBody(returnValue.toString());
        }

        try (Connection conn = DriverManager.getConnection(URL, USERNAME, PASSWORD)) {
            String sql = "SELECT username, age FROM users WHERE user_id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, userId);
                try (ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        returnValue.addProperty("userId", userId);
                        returnValue.addProperty("username", rs.getString("username"));
                        returnValue.addProperty("age", rs.getInt("age"));
                        return response.withStatusCode(200).withBody(returnValue.toString());
                    } else {
                        returnValue.addProperty("error", "User not found");
                        return response.withStatusCode(404).withBody(returnValue.toString());
                    }
                }
            }
        } catch (Exception e) {
            returnValue.addProperty("error", "Database connection failed: " + e.getMessage());
            return response.withStatusCode(500).withBody(returnValue.toString());
        }
    }
}
